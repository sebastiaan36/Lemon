<?php

use App\Models\HomepageContent;
use App\Models\Media;
use App\Services\BunnyStream;
use App\Support\MediaResolver;
use App\Support\VideoPoster;
use App\Support\VideoStream;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

uses(RefreshDatabase::class);

const POSTER_GUID = '99999999-8888-7777-6666-555555555555';

beforeEach(function () {
    Storage::fake('public');
    Bus::withoutDispatchingAfterResponses();
    Process::fake(['*' => Process::result(output: '', exitCode: 1)]);

    config([
        'app.url' => 'https://lemon.test',
        'services.bunny.stream.api_key' => 'test-key',
        'services.bunny.stream.library_id' => '123',
        'services.bunny.stream.cdn_hostname' => 'vz-test.b-cdn.net',
    ]);
});

function frameJpeg(int $width = 1000, int $height = 500): string
{
    return (string) ImageManager::gd()->create($width, $height)->fill('#2a9d8f')->toJpeg(quality: 90);
}

function fakeBunnyWithThumbnail(): void
{
    Http::fake([
        'video.bunnycdn.com/library/123/videos' => Http::response(['guid' => POSTER_GUID]),
        'video.bunnycdn.com/library/123/videos/*' => fn (Request $request) => match ($request->method()) {
            'GET' => Http::response(['status' => BunnyStream::STATUS_FINISHED, 'width' => 1920, 'height' => 1080, 'length' => 12]),
            default => Http::response(['success' => true]),
        },
        'vz-test.b-cdn.net/'.POSTER_GUID.'/thumbnail.jpg' => Http::response(frameJpeg(), 200, ['Content-Type' => 'image/jpeg']),
    ]);
}

function makePosterVideo(string $name = 'clip'): Media
{
    Storage::disk('public')->put("{$name}.mp4", 'fake-video-bytes');

    return Media::query()->create([
        'disk' => 'public', 'directory' => '', 'visibility' => 'public',
        'name' => $name, 'path' => "{$name}.mp4", 'ext' => 'mp4', 'type' => 'video/mp4', 'size' => 16,
    ]);
}

it('creates webp posters from the bunny thumbnail once encoding is finished', function () {
    fakeBunnyWithThumbnail();
    $media = makePosterVideo();

    VideoStream::refresh($media->refresh());

    $posters = VideoPoster::widths($media->refresh());

    expect(array_keys($posters))->toBe([640, 1000]);

    foreach ($posters as $path) {
        Storage::disk('public')->assertExists($path);
        expect($path)->toEndWith('w.webp');
    }

    Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/thumbnail.jpg')
        && $request->hasHeader('Referer', 'https://lemon.test/'));
});

it('falls back to an ffmpeg frame while bunny is not ready', function () {
    config(['services.bunny.stream.library_id' => null]);
    Process::fake(['*' => Process::result(output: frameJpeg(800, 450))]);

    $media = makePosterVideo()->refresh();

    expect(array_keys(VideoPoster::widths($media)))->toBe([640, 800]);
});

it('hands the poster srcset to the page and preloads it for a video hero', function () {
    fakeBunnyWithThumbnail();
    $media = makePosterVideo();
    VideoStream::refresh($media->refresh());

    HomepageContent::query()->create(['hero_bg_image' => $media->id]);

    $response = $this->get('/');
    $playlist = 'https://vz-test.b-cdn.net/'.POSTER_GUID.'/playlist.m3u8';

    $response->assertOk()
        ->assertSee('-poster-1000w.webp', false)
        ->assertSee('imagesizes="(max-width: 767px) 50vw, 100vw"', false)
        ->assertInertia(fn ($page) => $page
            ->where('heroBgImage', $playlist)
            ->where('mediaSources', fn ($sources): bool => str_ends_with($sources[$playlist]['poster'], '-poster-1000w.webp')
                && str_contains($sources[$playlist]['posterSrcset'], '-poster-640w.webp 640w')));

    expect(app(MediaResolver::class)->shareImageFor($playlist))->toEndWith('-poster-1000w.webp');
});

it('removes the posters when the video is deleted', function () {
    fakeBunnyWithThumbnail();
    $media = makePosterVideo();
    VideoStream::refresh($media->refresh());
    $paths = array_values(VideoPoster::widths($media->refresh()));

    $media->delete();

    foreach ($paths as $path) {
        Storage::disk('public')->assertMissing($path);
    }
});

it('creates missing posters with the artisan command', function () {
    config(['services.bunny.stream.library_id' => null]);
    $media = makePosterVideo();

    expect(VideoPoster::exists($media->refresh()))->toBeFalse();

    Process::fake(['*' => Process::result(output: frameJpeg(1280, 720))]);

    $this->artisan('media:video-posters')->assertSuccessful();

    expect(VideoPoster::exists($media->refresh()))->toBeTrue();
});
