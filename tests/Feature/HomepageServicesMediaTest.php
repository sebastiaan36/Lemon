<?php

use App\Models\HomepageContent;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    Bus::withoutDispatchingAfterResponses();
});

it('moves legacy service uploads into the media library', function () {
    Storage::disk('public')->put('homepage/services/brand.mp4', 'video-bytes');

    $content = HomepageContent::getSingleton();
    $content->services = [
        ['name' => 'Brand', 'mediaUrl' => 'homepage/services/brand.mp4', 'mediaType' => 'image'],
        ['name' => 'Experience', 'mediaUrl' => null, 'mediaType' => 'image'],
        ['name' => 'Employee', 'mediaUrl' => 'homepage/services/ontbreekt.mp4', 'mediaType' => 'video'],
    ];
    $content->saveQuietly();

    $migration = require collect(glob(database_path('migrations/*_move_homepage_service_media_to_curator.php')))->first();
    $migration->up();

    $services = $content->refresh()->services;
    $media = Media::query()->findOrFail($services[0]['media']);

    expect($media->type)->toBe('video/mp4')
        ->and($media->title)->toBe('Brand')
        ->and($media->directory)->toBe('homepage/services')
        ->and($services[0])->not->toHaveKeys(['mediaUrl', 'mediaType'])
        ->and($services[1]['media'])->toBeNull()
        ->and($services[2]['media'])->toBeNull();

    Storage::disk('public')->assertExists($media->path);

    $count = Media::query()->count();
    $migration->up();
    expect(Media::query()->count())->toBe($count);
});

it('serves service media from the library and derives the media type', function () {
    Storage::disk('public')->put('clip.mp4', 'video-bytes');
    $video = Media::query()->create([
        'disk' => 'public', 'directory' => '', 'visibility' => 'public',
        'name' => 'clip', 'path' => 'clip.mp4', 'ext' => 'mp4', 'type' => 'video/mp4', 'size' => 11,
    ]);

    $content = HomepageContent::getSingleton();
    $content->services = [
        ['name' => 'Brand', 'description' => 'x', 'tags' => [], 'media' => $video->id],
        ['name' => 'Experience', 'description' => 'y', 'tags' => [], 'media' => null],
    ];
    $content->saveQuietly();

    $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('services.0.mediaType', 'video')
        ->where('services.0.mediaUrl', fn (string $url): bool => str_ends_with($url, '.mp4'))
        ->missing('services.0.media')
        ->where('services.1.mediaUrl', null)
        ->where('services.1.mediaType', 'image'));
});
