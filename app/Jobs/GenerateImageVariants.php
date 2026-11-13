<?php

namespace App\Jobs;

use App\Models\Media;
use App\Support\ImageVariants;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Converts an uploaded image to WebP/AVIF. Dispatched after the response, so
 * the admin does not wait for encoding and no queue worker is required.
 */
class GenerateImageVariants
{
    use Dispatchable;

    public function __construct(public string $mediaId) {}

    public function handle(): void
    {
        $media = Media::query()->find($this->mediaId);

        if ($media) {
            ImageVariants::generate($media);
        }
    }
}
