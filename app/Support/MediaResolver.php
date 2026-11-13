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

    public function url(mixed $id): ?string
    {
        $media = $this->find($id);

        return $media ? self::applyCdn($media->url) : null;
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
