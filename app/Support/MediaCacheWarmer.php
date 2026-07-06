<?php

namespace App\Support;

use App\Models\Media;
use Awcodes\Curator\Config\GlideManager;
use Awcodes\Curator\Facades\Curator;

class MediaCacheWarmer
{
    /**
     * The Glide derivatives used by the admin panel (thumbnail, medium and large),
     * matching the parameters of Curator's GlideUrlProvider exactly so the
     * pre-generated files hit the same cache entries.
     *
     * @var array<int, array<string, int|string>>
     */
    public const array DERIVATIVES = [
        ['w' => 200, 'h' => 200, 'fm' => 'webp', 'fit' => 'crop'],
        ['w' => 640, 'h' => 640, 'fm' => 'webp', 'fit' => 'crop'],
        ['w' => 1024, 'h' => 1024, 'fm' => 'webp', 'fit' => 'contain'],
    ];

    public static function warm(Media $media): bool
    {
        if (! Curator::isResizable($media->ext)) {
            return false;
        }

        $server = app(GlideManager::class)->getServer();

        foreach (self::DERIVATIVES as $params) {
            $server->makeImage($media->path, $params);
        }

        return true;
    }
}
