<?php

namespace App\Models;

use App\Jobs\GenerateImageVariants;
use App\Jobs\UploadVideoToBunnyStream;
use App\Models\Concerns\LogsActivity;
use App\Support\ImageVariants;
use App\Support\MediaCacheWarmer;
use App\Support\SvgSanitizer;
use App\Support\VideoStream;
use Awcodes\Curator\Models\Media as CuratorMedia;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;

class Media extends CuratorMedia
{
    use HasUuids;
    use LogsActivity;

    protected $casts = [
        'exif' => 'array',
        'curations' => 'array',
        'custom_properties' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $media): void {
            if (SvgSanitizer::isSvg($media->ext, $media->type)) {
                SvgSanitizer::sanitizeStoredFile($media->disk, $media->path);
                $media->size = Storage::disk($media->disk)->size($media->path);
            }

            $suffix = random_int(100000, 999999);
            $newName = $media->name.'-'.$suffix;
            $needle = $media->name.'.'.$media->ext;
            $pos = strrpos($media->path, $needle);

            if ($pos === false) {
                return;
            }

            $newPath = substr_replace($media->path, $newName.'.'.$media->ext, $pos, strlen($needle));

            Storage::disk($media->disk)->move($media->path, $newPath);
            $media->name = $newName;
            $media->path = $newPath;
        });

        static::updating(function (self $media): void {
            if ($media->isDirty('path') && SvgSanitizer::isSvg($media->ext, $media->type)) {
                SvgSanitizer::sanitizeStoredFile($media->disk, $media->path);
                $media->size = Storage::disk($media->disk)->size($media->path);
            }
        });

        static::created(function (self $media): void {
            try {
                MediaCacheWarmer::warm($media);
            } catch (\Throwable) {
                // Thumbnails worden alsnog on-the-fly gegenereerd als voorverwarmen mislukt.
            }

            if (ImageVariants::isConvertible($media)) {
                GenerateImageVariants::dispatchAfterResponse((string) $media->getKey());
            }

            if (VideoStream::isStreamable($media)) {
                UploadVideoToBunnyStream::dispatchAfterResponse((string) $media->getKey());
            }
        });

        static::updated(function (self $media): void {
            $fileReplaced = $media->wasChanged(['size', 'ext', 'disk']);

            if ($fileReplaced || $media->wasChanged('path')) {
                ImageVariants::delete($media, $media->getOriginal('disk'));
                GenerateImageVariants::dispatchAfterResponse((string) $media->getKey());
            }

            if ($fileReplaced && VideoStream::isStreamable($media)) {
                UploadVideoToBunnyStream::dispatchAfterResponse((string) $media->getKey());
            }
        });

        static::deleted(function (self $media): void {
            ImageVariants::delete($media);
            VideoStream::deleteRemote($media);
        });
    }
}
