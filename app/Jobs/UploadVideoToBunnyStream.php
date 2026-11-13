<?php

namespace App\Jobs;

use App\Models\Media;
use App\Support\VideoStream;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Sends an uploaded video to Bunny Stream. Dispatched after the response,
 * so the admin does not wait for the upload and no queue worker is needed.
 */
class UploadVideoToBunnyStream
{
    use Dispatchable;

    public function __construct(public string $mediaId) {}

    public function handle(): void
    {
        $media = Media::query()->find($this->mediaId);

        if (! $media) {
            return;
        }

        set_time_limit(0);

        VideoStream::upload($media);
    }
}
