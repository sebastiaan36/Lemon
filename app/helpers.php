<?php

use App\Models\Media;

if (! function_exists('mediaUrl')) {
    function mediaUrl(?string $id): ?string
    {
        if (blank($id)) {
            return null;
        }

        $media = Media::find($id);
        if (! $media) {
            return null;
        }

        $url = $media->url;
        $cdnBase = config('services.bunny.cdn_url');

        if ($cdnBase && $url) {
            $path = parse_url($url, PHP_URL_PATH);

            return rtrim($cdnBase, '/').($path ?? '');
        }

        return $url;
    }
}

if (! function_exists('mediaUrls')) {
    /**
     * @param  array<mixed>|null  $ids
     * @return array<string>
     */
    function mediaUrls(?array $ids): array
    {
        if (blank($ids)) {
            return [];
        }

        return collect($ids)
            ->map(function ($id): ?string {
                if (is_array($id)) {
                    $id = $id['id'] ?? null;
                }

                return $id ? mediaUrl((string) $id) : null;
            })
            ->filter()
            ->values()
            ->all();
    }
}

if (! function_exists('mediaItem')) {
    /**
     * @return array{url: string, type: string, width: ?int, height: ?int}|null
     */
    function mediaItem(?string $id): ?array
    {
        if (blank($id)) {
            return null;
        }

        $media = Media::find($id);
        if (! $media) {
            return null;
        }

        $url = mediaUrl($id);
        if (blank($url)) {
            return null;
        }

        $type = str_starts_with((string) $media->type, 'video') ? 'video' : 'image';

        return [
            'url' => $url,
            'type' => $type,
            'width' => $media->width ? (int) $media->width : null,
            'height' => $media->height ? (int) $media->height : null,
        ];
    }
}

if (! function_exists('mediaItems')) {
    /**
     * @param  array<mixed>|null  $ids
     * @return array<int, array{url: string, type: string, width: ?int, height: ?int}>
     */
    function mediaItems(?array $ids): array
    {
        if (blank($ids)) {
            return [];
        }

        return collect($ids)
            ->map(function ($id): ?array {
                if (is_array($id)) {
                    $id = $id['id'] ?? null;
                }

                return $id ? mediaItem((string) $id) : null;
            })
            ->filter()
            ->values()
            ->all();
    }
}
