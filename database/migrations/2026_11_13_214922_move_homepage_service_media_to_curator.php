<?php

use App\Models\HomepageContent;
use App\Models\Media;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

/**
 * The homepage services used a plain FileUpload (`mediaUrl` = path on the
 * public disk + a manual `mediaType`). They now pick from the media library
 * (`media` = Curator id), so videos go through Bunny Stream and images get
 * WebP/AVIF variants. Existing files are registered as media items in place.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (HomepageContent::query()->get() as $content) {
            $services = $content->services;

            if (! is_array($services)) {
                continue;
            }

            $changed = false;

            foreach ($services as $index => $service) {
                if (! is_array($service) || array_key_exists('media', $service)) {
                    continue;
                }

                $path = $service['mediaUrl'] ?? null;
                $path = is_array($path) ? (array_values($path)[0] ?? null) : $path;

                $services[$index]['media'] = is_string($path) && $path !== ''
                    ? $this->registerMedia($path, $service['name'] ?? null)
                    : null;

                unset($services[$index]['mediaUrl'], $services[$index]['mediaType']);
                $changed = true;
            }

            if ($changed) {
                $content->services = $services;
                $content->saveQuietly();
            }
        }
    }

    public function down(): void
    {
        //
    }

    private function registerMedia(string $path, ?string $title): ?string
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $dimensions = str_starts_with((string) $disk->mimeType($path), 'image/')
            ? @getimagesize($disk->path($path))
            : false;

        $media = Media::query()->create([
            'disk' => 'public',
            'directory' => dirname($path) === '.' ? '' : dirname($path),
            'visibility' => 'public',
            'name' => pathinfo($path, PATHINFO_FILENAME),
            'path' => $path,
            'ext' => $extension,
            'type' => $disk->mimeType($path) ?: 'application/octet-stream',
            'size' => $disk->size($path),
            'width' => $dimensions ? $dimensions[0] : null,
            'height' => $dimensions ? $dimensions[1] : null,
            'title' => $title,
        ]);

        return (string) $media->getKey();
    }
};
