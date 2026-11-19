<?php

namespace App\Support;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;

/**
 * Request-scoped lookup for Curator media.
 *
 * Every media record is fetched at most once per request, and callers can
 * prime a batch of ids up front so a page needs a single `whereIn` query
 * instead of one query per image.
 */
class MediaResolver
{
    /**
     * @var array<string, Media|null>
     */
    private array $cache = [];

    /**
     * Media handed out through url() during this request, keyed by that URL.
     *
     * @var array<string, Media>
     */
    private array $handedOut = [];

    /**
     * Load all given media ids in one query. Accepts ids, arrays of ids and
     * Curator's `['id' => …]` shaped entries, nested at any depth.
     */
    public function prime(mixed ...$values): void
    {
        $ids = array_values(array_diff(
            array_unique(self::extractIds($values)),
            array_keys($this->cache),
        ));

        if ($ids === []) {
            return;
        }

        $found = Media::query()->whereIn('id', $ids)->get()->keyBy(fn (Media $media): string => (string) $media->getKey());

        foreach ($ids as $id) {
            $this->cache[$id] = $found->get($id);
        }
    }

    public function find(mixed $id): ?Media
    {
        $id = self::normaliseId($id);

        if ($id === null) {
            return null;
        }

        if (! array_key_exists($id, $this->cache)) {
            $this->prime($id);
        }

        return $this->cache[$id];
    }

    /**
     * Public URL of the media item: the Bunny Stream HLS playlist for videos
     * that finished encoding, the WebP variant for converted images (every
     * current browser supports it and it is much smaller), else the upload.
     */
    public function url(mixed $id): ?string
    {
        $media = $this->find($id);

        if (! $media) {
            return null;
        }

        $url = VideoStream::playlistUrl($media)
            ?? $this->sources($media)['webp']
            ?? $this->originalUrl($media);

        if ($url !== null) {
            $this->handedOut[$url] = $media;
        }

        return $url;
    }

    /**
     * URL of the uploaded file itself, e.g. for Open Graph images where not
     * every social network accepts WebP or AVIF.
     */
    public function originalUrl(mixed $id): ?string
    {
        $media = $id instanceof Media ? $id : $this->find($id);

        return $media ? self::applyCdn($media->url) : null;
    }

    /**
     * URLs of the generated modern-format variants, keyed by format.
     *
     * @return array{avif?: string, webp?: string}
     */
    public function sources(mixed $id): array
    {
        $media = $id instanceof Media ? $id : $this->find($id);

        if (! $media) {
            return [];
        }

        return array_filter(array_map(
            fn (string $path): ?string => self::applyCdn(Media::resolveUrl($media->disk, $path, $media->visibility)),
            ImageVariants::paths($media),
        ));
    }

    /**
     * `srcset` strings per format, built from all generated widths.
     *
     * @return array{avif?: string, webp?: string}
     */
    public function srcsets(Media $media): array
    {
        return array_map(
            fn (array $byWidth): string => collect($byWidth)
                ->map(fn (string $path, int $width): string => self::applyCdn(Media::resolveUrl($media->disk, $path, $media->visibility))." {$width}w")
                ->implode(', '),
            ImageVariants::widths($media),
        );
    }

    /**
     * Extra sources for every media URL handed out during this request, keyed
     * by that URL. <CmsImage> looks its `src` up here for AVIF/WebP srcsets
     * and intrinsic dimensions; <HlsVideo> for the poster (own WebP srcset, or
     * Bunny Stream's thumbnail until that exists).
     *
     * @return array<string, array{avif?: string, webp?: string, width?: int, height?: int, poster?: string, posterSrcset?: string}>
     */
    public function manifest(): array
    {
        $manifest = [];

        foreach ($this->handedOut as $url => $media) {
            if (VideoStream::isStreamable($media)) {
                $poster = $this->posterSources($media);

                if ($poster !== null) {
                    $manifest[$url] = $poster;
                }

                continue;
            }

            $entry = $this->srcsets($media);

            if ($entry === []) {
                continue;
            }

            $manifest[$url] = array_merge($entry, ImageVariants::fullSize($media) ?? []);
        }

        return $manifest;
    }

    /**
     * Map a URL handed out by url() back to the original upload's URL.
     */
    public function originalUrlFor(?string $url): ?string
    {
        $media = $url === null ? null : ($this->handedOut[$url] ?? null);

        return $media ? $this->originalUrl($media) : $url;
    }

    /**
     * Image for social sharing (og:image) for a URL handed out by url(): the
     * original upload for images, Bunny's thumbnail for streamed videos.
     */
    public function shareImageFor(?string $url): ?string
    {
        $media = $url === null ? null : ($this->handedOut[$url] ?? null);

        if ($media && VideoStream::isStreamable($media)) {
            return $this->posterSources($media)['poster'] ?? null;
        }

        return $this->originalUrlFor($url);
    }

    /**
     * `<link rel="preload">` attributes for a hero image, matching the AVIF
     * (or else WebP) source <CmsImage> renders, so the browser starts the
     * download before the JavaScript has run. For videos this is the WebP
     * poster. Null for unknown URLs and videos without a generated poster.
     *
     * @return array{href: string, imagesrcset: string, type: string}|null
     */
    public function preloadFor(?string $url): ?array
    {
        $media = $url === null ? null : ($this->handedOut[$url] ?? null);

        if (! $media) {
            return null;
        }

        if (VideoStream::isStreamable($media)) {
            $poster = $this->posterSources($media);

            return isset($poster['posterSrcset']) ? [
                'href' => $poster['poster'],
                'imagesrcset' => $poster['posterSrcset'],
                'type' => 'image/webp',
            ] : null;
        }

        $srcsets = $this->srcsets($media);
        $format = isset($srcsets['avif']) ? 'avif' : (isset($srcsets['webp']) ? 'webp' : null);

        if ($format === null) {
            return null;
        }

        return [
            'href' => $this->sources($media)[$format],
            'imagesrcset' => $srcsets[$format],
            'type' => "image/{$format}",
        ];
    }

    /**
     * Poster for a video: the generated WebP widths, else Bunny's thumbnail.
     *
     * @return array{poster: string, posterSrcset?: string}|null
     */
    private function posterSources(Media $media): ?array
    {
        $widths = VideoPoster::widths($media);

        if ($widths !== []) {
            $urls = array_map(
                fn (string $path): string => (string) self::applyCdn(Media::resolveUrl($media->disk, $path, $media->visibility)),
                $widths,
            );

            return [
                'poster' => end($urls),
                'posterSrcset' => collect($urls)->map(fn (string $url, int $width): string => "{$url} {$width}w")->implode(', '),
            ];
        }

        $bunny = VideoStream::posterUrl($media);

        return $bunny === null ? null : ['poster' => $bunny];
    }

    /**
     * Public URL for a file on the public disk (FileUpload fields), using the
     * same base URL and CDN rewrite as Curator media.
     */
    public static function publicDiskUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return self::applyCdn(Storage::disk('public')->url($path));
    }

    public static function applyCdn(?string $url): ?string
    {
        $cdnBase = config('services.bunny.cdn_url');

        if (blank($url) || blank($cdnBase)) {
            return $url;
        }

        return rtrim($cdnBase, '/').(parse_url($url, PHP_URL_PATH) ?? '');
    }

    private static function normaliseId(mixed $id): ?string
    {
        if (is_array($id)) {
            $id = $id['id'] ?? null;
        }

        if (! is_scalar($id) || blank($id)) {
            return null;
        }

        return (string) $id;
    }

    /**
     * @param  array<mixed>  $values
     * @return array<int, string>
     */
    private static function extractIds(array $values): array
    {
        $ids = [];

        foreach ($values as $value) {
            if (is_array($value) && ! array_key_exists('id', $value)) {
                array_push($ids, ...self::extractIds($value));

                continue;
            }

            $id = self::normaliseId($value);

            if ($id !== null) {
                $ids[] = $id;
            }
        }

        return $ids;
    }
}
