<?php

namespace App\Jobs;

use App\Models\Media;
use App\Support\VideoPoster;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Creates the WebP poster for an uploaded video. Dispatched after the
 * response, so the admin does not wait and no queue worker is required.
 */
class GenerateVideoPoster
{
    use Dispatchable;

    public function __construct(public string $mediaId) {}

    public function handle(): void
    {
        $media = Media::query()->find($this->mediaId);

        if ($media) {
            VideoPoster::generate($media);
        }
    }
}
