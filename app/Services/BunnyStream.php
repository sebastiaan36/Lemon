<?php

namespace App\Services;

use GuzzleHttp\Psr7\Utils;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin client for the Bunny Stream API (video.bunnycdn.com).
 *
 * Bunny transcodes uploaded videos into an adaptive HLS stream in the
 * resolutions enabled on the library, so mobile devices automatically get
 * a lighter version than desktops.
 *
 * @see https://docs.bunny.net/reference/video_createvideo
 */
class BunnyStream
{
    /** Bunny's encoding status codes. */
    public const int STATUS_CREATED = 0;

    public const int STATUS_UPLOADED = 1;

    public const int STATUS_PROCESSING = 2;

    public const int STATUS_TRANSCODING = 3;

    public const int STATUS_FINISHED = 4;

    public const int STATUS_ERROR = 5;

    public const int STATUS_UPLOAD_FAILED = 6;

    public function __construct(
        private readonly ?string $apiKey,
        private readonly ?string $libraryId,
        private readonly ?string $cdnHostname,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            config('services.bunny.stream.api_key'),
            config('services.bunny.stream.library_id'),
            config('services.bunny.stream.cdn_hostname'),
        );
    }

    public function isConfigured(): bool
    {
        return filled($this->apiKey) && filled($this->libraryId) && filled($this->cdnHostname);
    }

    /**
     * Create an empty video object and return its GUID.
     */
    public function createVideo(string $title): string
    {
        $guid = $this->request()
            ->post($this->libraryPath('videos'), ['title' => $title])
            ->throw()
            ->json('guid');

        if (! is_string($guid) || $guid === '') {
            throw new RuntimeException('Bunny Stream did not return a video GUID.');
        }

        return $guid;
    }

    /**
     * Upload the video file. The resource is streamed, so large files are
     * not loaded into memory.
     *
     * @param  resource  $stream
     */
    public function upload(string $guid, $stream): void
    {
        $this->request(retry: false)
            ->timeout(60 * 30)
            ->withBody(Utils::streamFor($stream), 'application/octet-stream')
            ->put($this->libraryPath("videos/{$guid}"))
            ->throw();
    }

    /**
     * @return array{status: int, width: int, height: int, length: int}
     */
    public function getVideo(string $guid): array
    {
        $video = $this->request()->get($this->libraryPath("videos/{$guid}"))->throw()->json();

        return [
            'status' => (int) ($video['status'] ?? self::STATUS_ERROR),
            'width' => (int) ($video['width'] ?? 0),
            'height' => (int) ($video['height'] ?? 0),
            'length' => (int) ($video['length'] ?? 0),
        ];
    }

    public function deleteVideo(string $guid): void
    {
        $response = $this->request()->delete($this->libraryPath("videos/{$guid}"));

        if ($response->status() !== 404) {
            $response->throw();
        }
    }

    public function playlistUrl(string $guid): string
    {
        return "https://{$this->cdnHostname}/{$guid}/playlist.m3u8";
    }

    public function thumbnailUrl(string $guid): string
    {
        return "https://{$this->cdnHostname}/{$guid}/thumbnail.jpg";
    }

    /**
     * An upload body is a one-shot stream, so uploads are never retried.
     */
    private function request(bool $retry = true): PendingRequest
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Bunny Stream is not configured (BUNNY_STREAM_* in .env).');
        }

        $request = Http::baseUrl('https://video.bunnycdn.com')
            ->withHeaders(['AccessKey' => $this->apiKey])
            ->acceptJson()
            ->timeout(30);

        return $retry ? $request->retry(2, 500, throw: false) : $request;
    }

    private function libraryPath(string $path): string
    {
        return "library/{$this->libraryId}/{$path}";
    }
}
