<?php

namespace App\Models;

use Awcodes\Curator\Models\Media as CuratorMedia;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Storage;

class Media extends CuratorMedia
{
    use HasUuids;

    protected $casts = [
        'exif' => 'array',
        'curations' => 'array',
        'custom_properties' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $media): void {
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
    }
}
