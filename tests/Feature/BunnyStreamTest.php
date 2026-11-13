<?php

use App\Models\Media;
use App\Services\BunnyStream;
use App\Support\MediaResolver;
use App\Support\VideoStream;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

const BUNNY_GUID = '11111111-2222-3333-4444-555555555555';

beforeEach(function () {
    Storage::fake('public');
    Bus::withoutDispatchingAfterResponses();

    config([
        'services.bunny.stream.api_key' => 'test-key',
        'services.bunny.stream.library_id' => '123',
        'services.bunny.stream.cdn_hostname' => 'vz-test.b-cdn.net',
    ]);
});

function fakeBunny(int $status = BunnyStream::STATUS_FINISHED): void
{
    Http::fake([
        'video.bunnycdn.com/library/123/videos' => Http::response(['guid' => BUNNY_GUID]),
        'video.bunnycdn.com/library/123/videos/*' => fn (Request $request) => match ($request->method()) {
            'GET' => Http::response(['status' => $status, 'width' => 1920, 'height' => 1080, 'length' => 12]),
            default => Http::response(['success' => true]),
        },
    ]);
}

function makeVideo(): Media
{
    Storage::disk('public')->put('clip.mp4', 'fake-video-bytes');

    return Media::query()->create([
        'disk' => 'public', 'directory' => '', 'visibility' => 'public',
        'name' => 'clip', 'path' => 'clip.mp4', 'ext' => 'mp4', 'type' => 'video/mp4', 'size' => 16,
        'title' => 'Showreel',
    ]);
}

it('uploads a new video to bunny stream', function () {
    fakeBunny();

    $media = makeVideo()->refresh();

    expect(VideoStream::guid($media))->toBe(BUNNY_GUID)
        ->and(VideoStream::state($media)['status'])->toBe(BunnyStream::STATUS_UPLOADED);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'POST'
        && $request->hasHeader('AccessKey', 'test-key')
        && $request['title'] === 'Showreel');
    Http::assertSent(fn (Request $request): bool => $request->method() === 'PUT'
        && str_ends_with($request->url(), '/videos/'.BUNNY_GUID)
        && $request->body() === 'fake-video-bytes');
});

it('keeps serving the original file until bunny has finished encoding', function () {
    fakeBunny(BunnyStream::STATUS_TRANSCODING);
    $media = makeVideo();

    expect(mediaUrl($media->id))->toEndWith('.mp4');

    VideoStream::refresh($media->refresh());
    app()->forgetScopedInstances();

    expect(mediaUrl($media->id))->toEndWith('.mp4');
});

it('serves the hls playlist and poster once encoding is finished', function () {
    fakeBunny();
    $media = makeVideo();
    VideoStream::refresh($media->refresh());

    $url = mediaUrl($media->id);
    $resolver = app(MediaResolver::class);

    expect($url)->toBe('https://vz-test.b-cdn.net/'.BUNNY_GUID.'/playlist.m3u8')
        ->and($resolver->manifest()[$url]['poster'])->toBe('https://vz-test.b-cdn.net/'.BUNNY_GUID.'/thumbnail.jpg')
        ->and($resolver->shareImageFor($url))->toEndWith('/thumbnail.jpg')
        ->and($resolver->preloadFor($url))->toBeNull();
});

it('refreshes the status when bunny calls the webhook', function () {
    fakeBunny();
    $media = makeVideo();

    $this->postJson('/webhooks/bunny-stream', ['VideoLibraryId' => 123, 'VideoGuid' => BUNNY_GUID, 'Status' => 3])
        ->assertOk()
        ->assertJson(['status' => BunnyStream::STATUS_FINISHED]);

    expect(VideoStream::isReady($media->refresh()))->toBeTrue();
});

it('ignores webhooks for other libraries or unknown videos', function () {
    fakeBunny();
    makeVideo();
    Http::fake();

    $this->postJson('/webhooks/bunny-stream', ['VideoLibraryId' => 999, 'VideoGuid' => BUNNY_GUID])->assertJson(['ignored' => true]);
    $this->postJson('/webhooks/bunny-stream', ['VideoLibraryId' => 123, 'VideoGuid' => 'onbekend'])->assertJson(['ignored' => true]);

    Http::assertNothingSent();
});

it('deletes the bunny video together with the media', function () {
    fakeBunny();
    $media = makeVideo()->refresh();

    $media->delete();

    Http::assertSent(fn (Request $request): bool => $request->method() === 'DELETE'
        && str_ends_with($request->url(), '/videos/'.BUNNY_GUID));
});

it('re-uploads when the file is replaced but not when it is only renamed', function () {
    fakeBunny();
    $media = makeVideo()->refresh();

    $media->update(['title' => 'Nieuwe titel']);
    Http::assertSentCount(2);

    Storage::disk('public')->put($media->path, 'other-video-bytes');
    $media->update(['size' => 17]);

    Http::assertSent(fn (Request $request): bool => $request->method() === 'DELETE');
    Http::assertSentCount(5);
});

it('does nothing when bunny stream is not configured', function () {
    config(['services.bunny.stream.library_id' => null]);
    Http::fake();

    $media = makeVideo()->refresh();

    expect(VideoStream::guid($media))->toBeNull()
        ->and(mediaUrl($media->id))->toEndWith('.mp4');
    Http::assertNothingSent();
});

it('uploads existing videos and refreshes their status with the artisan command', function () {
    config(['services.bunny.stream.library_id' => null]);
    $media = makeVideo();

    config(['services.bunny.stream.library_id' => '123']);
    fakeBunny();

    $this->artisan('media:bunny-stream')
        ->expectsOutputToContain('1 beschikbaar via Bunny Stream')
        ->assertSuccessful();

    expect(VideoStream::isReady($media->refresh()))->toBeTrue();
});
