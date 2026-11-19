<?php

namespace App\Support;

use App\Models\Media;
use App\Services\BunnyStream;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\ExecutableFinder;
use Throwable;

/**
 * Lightweight WebP poster images for uploaded videos, shown while the video
 * itself is still loading.
 *
 * The frame comes from Bunny Stream's thumbnail once the video is encoded,
 * or else from the local file via ffmpeg (when installed). It is stored next
 * to the video in several widths (`clip-123-poster-640w.webp`, …) and the
 * paths are recorded in `custom_properties.poster` as [width => path].
 */
class VideoPoster
{
    /**
     * Widths to generate; the largest also caps the full-size poster.
     *
     * @var array<int, int>
     */
    public const array WIDTHS = [640, 1280, 1920];

    public const int QUALITY = 75;

    /**
     * (Re)generate the poster. Returns the stored widths, or [] when no frame
     * could be obtained (Bunny not ready and no ffmpeg).
     *
     * @return array<int, string> width => path
     */
    public static function generate(Media $media): array
    {
        if (! VideoStream::isStreamable($media)) {
            return [];
        }

        $frame = self::frame($media);

        if ($frame === null) {
            return [];
        }

        try {
            $image = ImageVariants::manager()->read($frame);
            unset($frame);
        } catch (Throwable $e) {
            Log::warning('Video poster: could not read frame', ['media' => $media->getKey(), 'error' => $e->getMessage()]);

            return [];
        }

        self::delete($media);

        $storage = Storage::disk($media->disk);
        $base = self::basePath($media->path);
        $visibility = ['visibility' => $media->visibility ?: 'public'];
        $posters = [];

        // Largest first, shrinking the same image in place: no copies in memory.
        foreach (array_reverse(self::WIDTHS) as $width) {
            $image->scaleDown(width: $width);
            $actual = $image->width();

            if (isset($posters[$actual])) {
                continue;
            }

            $path = "{$base}-poster-{$actual}w.webp";
            $storage->put($path, (string) $image->toWebp(quality: self::QUALITY, strip: true), $visibility);
            $posters[$actual] = $path;
        }

        ksort($posters);
        self::store($media, $posters);

        return $posters;
    }

    /**
     * Poster paths, smallest width first.
     *
     * @return array<int, string>
     */
    public static function widths(Media $media): array
    {
        $posters = $media->custom_properties['poster'] ?? [];

        if (! is_array($posters)) {
            return [];
        }

        $posters = array_filter($posters, 'is_string');
        ksort($posters);

        return $posters;
    }

    public static function exists(Media $media): bool
    {
        return self::widths($media) !== [];
    }

    public static function delete(Media $media, ?string $disk = null): void
    {
        $paths = array_values(self::widths($media));

        if ($paths !== []) {
            Storage::disk($disk ?? $media->disk)->delete($paths);
        }

        if (array_key_exists('poster', $media->custom_properties ?? [])) {
            self::store($media, []);
        }
    }

    /**
     * Raw image bytes of a representative frame, or null.
     */
    private static function frame(Media $media): ?string
    {
        if (VideoStream::isReady($media)) {
            $frame = self::bunnyThumbnail($media);

            if ($frame !== null) {
                return $frame;
            }
        }

        return self::ffmpegFrame($media);
    }

    /**
     * Bunny's CDN only serves allowed referrers, so the request names the site.
     */
    private static function bunnyThumbnail(Media $media): ?string
    {
        $url = app(BunnyStream::class)->thumbnailUrl((string) VideoStream::guid($media));

        try {
            return Http::withHeaders(['Referer' => rtrim((string) config('app.url'), '/').'/'])
                ->timeout(30)
                ->get($url)
                ->throw()
                ->body();
        } catch (Throwable $e) {
            Log::warning('Video poster: Bunny thumbnail unavailable', ['media' => $media->getKey(), 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * First frame of the local file. Only for local disks with ffmpeg present.
     */
    private static function ffmpegFrame(Media $media): ?string
    {
        $ffmpeg = (new ExecutableFinder)->find('ffmpeg', null, ['/opt/homebrew/bin', '/usr/local/bin', '/usr/bin']);

        if (! $ffmpeg) {
            return null;
        }

        try {
            $source = Storage::disk($media->disk)->path($media->path);
        } catch (Throwable) {
            return null;
        }

        if (! is_file($source)) {
            return null;
        }

        $result = Process::timeout(60)->run([
            $ffmpeg, '-v', 'error', '-i', $source, '-frames:v', '1', '-f', 'image2pipe', '-vcodec', 'png', '-',
        ]);

        if ($result->failed() || $result->output() === '') {
            Log::warning('Video poster: ffmpeg failed', ['media' => $media->getKey(), 'error' => $result->errorOutput()]);

            return null;
        }

        return $result->output();
    }

    /**
     * Write without firing model events (no activity log, no re-upload).
     *
     * @param  array<int, string>  $posters
     */
    private static function store(Media $media, array $posters): void
    {
        $properties = $media->custom_properties ?? [];

        if ($posters === []) {
            unset($properties['poster']);
        } else {
            $properties['poster'] = $posters;
        }

        $media->custom_properties = $properties === [] ? null : $properties;
        $media->saveQuietly();
    }

    private static function basePath(string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        return $extension === '' ? $path : substr($path, 0, -strlen($extension) - 1);
    }
}
