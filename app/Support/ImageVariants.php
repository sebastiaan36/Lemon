<?php

namespace App\Support;

use App\Models\Media;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Throwable;

/**
 * Generates modern-format copies (WebP and AVIF) of uploaded raster images.
 *
 * The variants are stored next to the original on the same disk, in several
 * widths for `srcset`: `foto-123456.jpg` gets `foto-123456.webp` (full size,
 * capped at MAX_DIMENSION) plus `foto-123456-640w.webp`, `-1280w`, … and the
 * same set as AVIF. Their paths are recorded in the media's
 * `custom_properties.variants` as format => [width => path], so the frontend
 * only references files that really exist. The original is never touched and
 * stays the fallback for browsers (and crawlers) without support.
 */
class ImageVariants
{
    /**
     * Formats to generate, best compression first, with encoder quality.
     *
     * @var array<string, int>
     */
    public const array FORMATS = [
        'avif' => 60,
        'webp' => 80,
    ];

    /** Longest edge of a variant; larger originals are scaled down. */
    public const int MAX_DIMENSION = 2560;

    /**
     * Extra, smaller widths generated below the full-size variant.
     *
     * @var array<int, int>
     */
    public const array WIDTHS = [640, 1280, 1920];

    /** Static raster formats that are worth converting (no SVG, GIF animations or video). */
    public const array CONVERTIBLE = ['jpg', 'jpeg', 'png', 'webp', 'bmp', 'tif', 'tiff'];

    public static function isConvertible(Media $media): bool
    {
        return in_array(strtolower((string) $media->ext), self::CONVERTIBLE, true)
            && str_starts_with((string) $media->type, 'image/');
    }

    /**
     * Formats the current image driver can actually encode. AVIF depends on
     * how GD/ImageMagick was compiled on the server, so it may be missing.
     *
     * @return array<int, string>
     */
    public static function supportedFormats(): array
    {
        $driver = self::manager()->driver();

        return array_values(array_filter(
            array_keys(self::FORMATS),
            fn (string $format): bool => $driver->supports($format),
        ));
    }

    /**
     * (Re)generate all variants for the media item and store their paths.
     *
     * @return array<string, array<int, string>> format => [width => path]
     */
    public static function generate(Media $media): array
    {
        self::delete($media);

        $variants = [];

        if (self::isConvertible($media)) {
            $variants = self::encodeVariants($media);
        }

        self::storeVariantPaths($media, $variants);

        return $variants;
    }

    /**
     * Remove the variant files that are recorded on the media item.
     */
    public static function delete(Media $media, ?string $disk = null): void
    {
        $paths = collect(self::widths($media))->flatten()->all();

        if ($paths !== []) {
            Storage::disk($disk ?? $media->disk)->delete($paths);
        }
    }

    /**
     * Path of the largest variant per format.
     *
     * @return array<string, string> format => path
     */
    public static function paths(Media $media): array
    {
        return array_map(fn (array $byWidth): string => end($byWidth), self::widths($media));
    }

    /**
     * All variants per format, smallest width first. Variants stored before
     * multiple widths existed (a plain path) count as the full-size one.
     *
     * @return array<string, array<int, string>> format => [width => path]
     */
    public static function widths(Media $media): array
    {
        $variants = $media->custom_properties['variants'] ?? [];

        if (! is_array($variants)) {
            return [];
        }

        $result = [];

        foreach ($variants as $format => $byWidth) {
            if (is_string($byWidth)) {
                $byWidth = [self::fullSize($media)['width'] ?? self::MAX_DIMENSION => $byWidth];
            }

            if (! is_array($byWidth)) {
                continue;
            }

            $byWidth = array_filter($byWidth, 'is_string');
            ksort($byWidth);

            if ($byWidth !== []) {
                $result[(string) $format] = $byWidth;
            }
        }

        return $result;
    }

    /**
     * Whether the item still needs (re)conversion: it has no variants yet, or
     * only a single-width variant from before srcset support.
     */
    public static function needsConversion(Media $media): bool
    {
        $variants = $media->custom_properties['variants'] ?? null;

        if (! is_array($variants) || $variants === []) {
            return true;
        }

        return collect($variants)->contains(fn (mixed $byWidth): bool => is_string($byWidth));
    }

    /**
     * Dimensions of the full-size variant: the original, scaled down to fit
     * MAX_DIMENSION. Null when the original's dimensions are unknown.
     *
     * @return array{width: int, height: int}|null
     */
    public static function fullSize(Media $media): ?array
    {
        $width = (int) $media->width;
        $height = (int) $media->height;

        if ($width <= 0 || $height <= 0) {
            return null;
        }

        $scale = min(1, self::MAX_DIMENSION / max($width, $height));

        return [
            'width' => (int) round($width * $scale),
            'height' => (int) round($height * $scale),
        ];
    }

    /**
     * Encode every supported format in all widths, skipping the original's
     * own format and any format whose full-size variant would not be smaller
     * than the original file.
     *
     * @return array<string, array<int, string>>
     */
    private static function encodeVariants(Media $media): array
    {
        $storage = Storage::disk($media->disk);

        try {
            $image = self::manager()->read($storage->get($media->path));
        } catch (Throwable $e) {
            Log::warning('Image variants: could not read original', ['media' => $media->getKey(), 'error' => $e->getMessage()]);

            return [];
        }

        $image->scaleDown(self::MAX_DIMENSION, self::MAX_DIMENSION);

        $originalSize = (int) ($media->size ?: $storage->size($media->path));
        $base = self::basePath($media->path);
        $fullWidth = $image->width();
        $variants = [];

        foreach (self::supportedFormats() as $format) {
            if ($format === strtolower((string) $media->ext)) {
                continue;
            }

            try {
                $encoded = self::encode($image, $format);
            } catch (Throwable $e) {
                Log::warning("Image variants: {$format} encoding failed", ['media' => $media->getKey(), 'error' => $e->getMessage()]);

                continue;
            }

            if ($originalSize > 0 && strlen($encoded) >= $originalSize) {
                continue;
            }

            $visibility = ['visibility' => $media->visibility ?: 'public'];
            $storage->put("{$base}.{$format}", $encoded, $visibility);
            $variants[$format] = [$fullWidth => "{$base}.{$format}"];

            foreach (self::WIDTHS as $width) {
                if ($width >= $fullWidth) {
                    continue;
                }

                try {
                    $resized = (clone $image)->scaleDown(width: $width);
                    $path = "{$base}-{$width}w.{$format}";
                    $storage->put($path, self::encode($resized, $format), $visibility);
                    $variants[$format][$width] = $path;
                } catch (Throwable $e) {
                    Log::warning("Image variants: {$format} {$width}w encoding failed", ['media' => $media->getKey(), 'error' => $e->getMessage()]);
                }
            }

            ksort($variants[$format]);
        }

        return $variants;
    }

    private static function encode(ImageInterface $image, string $format): string
    {
        $quality = self::FORMATS[$format];

        return match ($format) {
            'avif' => (string) $image->toAvif(quality: $quality, strip: true),
            'webp' => (string) $image->toWebp(quality: $quality, strip: true),
        };
    }

    /**
     * Write the variant paths without firing model events, so this does not
     * trigger the activity log or the file handling in the observers again.
     *
     * @param  array<string, array<int, string>>  $variants
     */
    private static function storeVariantPaths(Media $media, array $variants): void
    {
        $properties = $media->custom_properties ?? [];

        if ($variants === []) {
            unset($properties['variants']);
        } else {
            $properties['variants'] = $variants;
        }

        $media->custom_properties = $properties === [] ? null : $properties;
        $media->saveQuietly();
    }

    private static function basePath(string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return $extension === '' ? $path : substr($path, 0, -strlen($extension) - 1);
    }

    public static function manager(): ImageManager
    {
        return extension_loaded('imagick') ? ImageManager::imagick() : ImageManager::gd();
    }
}
