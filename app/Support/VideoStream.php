<?php

namespace App\Support;

use App\Models\Media;
use App\Services\BunnyStream;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

/**
 * Keeps uploaded videos in sync with Bunny Stream.
 *
 * The Bunny state lives in the media's `custom_properties.bunny_stream`
 * (`guid`, `status`, `width`, `height`). The original file stays on our
 * disk: the site keeps serving it until Bunny has finished transcoding,
 * and it is the fallback if Stream is ever switched off.
 */
class VideoStream
{
    public static function isStreamable(Media $media): bool
    {
        return str_starts_with((string) $media->type, 'video/');
    }

    /**
     * @return array{guid?: string, status?: int, width?: int, height?: int}
     */
    public static function state(Media $media): array
    {
        $state = $media->custom_properties['bunny_stream'] ?? [];

        return is_array($state) ? $state : [];
    }

    public static function guid(Media $media): ?string
    {
        $guid = self::state($media)['guid'] ?? null;

        return is_string($guid) && $guid !== '' ? $guid : null;
    }

    public static function isReady(Media $media): bool
    {
        return self::guid($media) !== null
            && (self::state($media)['status'] ?? null) === BunnyStream::STATUS_FINISHED;
    }

    /**
     * Whether the video still has to be (re)uploaded: never sent to Bunny,
     * or Bunny reported a failed upload or encoding.
     */
    public static function needsUpload(Media $media): bool
    {
        return self::isStreamable($media) && (
            self::guid($media) === null
            || in_array(self::state($media)['status'] ?? null, [BunnyStream::STATUS_ERROR, BunnyStream::STATUS_UPLOAD_FAILED], true)
        );
    }

    /**
     * Whether Bunny is still working on the video.
     */
    public static function isPending(Media $media): bool
    {
        return self::guid($media) !== null
            && in_array(self::state($media)['status'] ?? null, [
                BunnyStream::STATUS_CREATED,
                BunnyStream::STATUS_UPLOADED,
                BunnyStream::STATUS_PROCESSING,
                BunnyStream::STATUS_TRANSCODING,
            ], true);
    }

    /**
     * Upload the media's file to Bunny Stream, replacing an earlier upload.
     */
    public static function upload(Media $media): void
    {
        $stream = app(BunnyStream::class);

        if (! self::isStreamable($media) || ! $stream->isConfigured()) {
            return;
        }

        self::deleteRemote($media);

        $guid = $stream->createVideo($media->title ?: $media->name);
        self::store($media, ['guid' => $guid, 'status' => BunnyStream::STATUS_CREATED]);

        $file = Storage::disk($media->disk)->readStream($media->path);

        if (! is_resource($file)) {
            self::store($media, ['guid' => $guid, 'status' => BunnyStream::STATUS_UPLOAD_FAILED]);

            throw new RuntimeException("Video file [{$media->path}] could not be read.");
        }

        try {
            $stream->upload($guid, $file);
        } catch (Throwable $e) {
            self::store($media, ['guid' => $guid, 'status' => BunnyStream::STATUS_UPLOAD_FAILED]);

            throw $e;
        }

        self::store($media, ['guid' => $guid, 'status' => BunnyStream::STATUS_UPLOADED]);
    }

    /**
     * Fetch the encoding status from Bunny and store it.
     */
    public static function refresh(Media $media): void
    {
        $guid = self::guid($media);

        if ($guid === null) {
            return;
        }

        $video = app(BunnyStream::class)->getVideo($guid);

        self::store($media, [
            'guid' => $guid,
            'status' => $video['status'],
            'width' => $video['width'],
            'height' => $video['height'],
        ]);
    }

    /**
     * Delete the video from Bunny. Failures are logged, never thrown, so
     * deleting or replacing media in the admin keeps working.
     */
    public static function deleteRemote(Media $media): void
    {
        $guid = self::guid($media);
        $stream = app(BunnyStream::class);

        if ($guid === null || ! $stream->isConfigured()) {
            return;
        }

        try {
            $stream->deleteVideo($guid);
        } catch (Throwable $e) {
            Log::warning('Bunny Stream: could not delete video', ['media' => $media->getKey(), 'guid' => $guid, 'error' => $e->getMessage()]);
        }
    }

    public static function playlistUrl(Media $media): ?string
    {
        return self::isReady($media) ? app(BunnyStream::class)->playlistUrl((string) self::guid($media)) : null;
    }

    public static function posterUrl(Media $media): ?string
    {
        return self::isReady($media) ? app(BunnyStream::class)->thumbnailUrl((string) self::guid($media)) : null;
    }

    /**
     * Write the Bunny state without firing model events (no activity log,
     * no re-upload through the model hooks).
     *
     * @param  array{guid: string, status: int, width?: int, height?: int}  $state
     */
    private static function store(Media $media, array $state): void
    {
        $properties = $media->custom_properties ?? [];
        $properties['bunny_stream'] = $state;

        $media->custom_properties = $properties;
        $media->saveQuietly();
    }
}
