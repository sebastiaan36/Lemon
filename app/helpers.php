<?php

use App\Support\MediaResolver;

if (! function_exists('primeMedia')) {
    /**
     * Batch-load media ids (ids, arrays of ids or nested arrays) so later
     * mediaUrl()/mediaItem() calls hit the request cache instead of the database.
     */
    function primeMedia(mixed ...$values): void
    {
        app(MediaResolver::class)->prime(...$values);
    }
}

if (! function_exists('mediaUrl')) {
    function mediaUrl(?string $id): ?string
    {
        return app(MediaResolver::class)->url($id);
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

        $resolver = app(MediaResolver::class);
        $resolver->prime($ids);

        return collect($ids)
            ->map(fn (mixed $id): ?string => $resolver->url($id))
            ->filter()
            ->values()
            ->all();
    }
}

if (! function_exists('mediaItem')) {
    /**
     * @return array{url: string, type: string, width: ?int, height: ?int}|null
     */
    function mediaItem(mixed $id): ?array
    {
        $resolver = app(MediaResolver::class);
        $media = $resolver->find($id);

        if (! $media) {
            return null;
        }

        $url = $resolver->url($id);

        if (blank($url)) {
            return null;
        }

        return [
            'url' => $url,
            'type' => str_starts_with((string) $media->type, 'video') ? 'video' : 'image',
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

        app(MediaResolver::class)->prime($ids);

        return collect($ids)
            ->map(fn (mixed $id): ?array => mediaItem($id))
            ->filter()
            ->values()
            ->all();
    }
}

if (! function_exists('publicStorageUrl')) {
    /**
     * URL for a FileUpload path on the public disk, with the same CDN handling as Curator media.
     */
    function publicStorageUrl(?string $path): ?string
    {
        return MediaResolver::publicDiskUrl($path);
    }
}
