<?php

use App\Filament\Curator\Pages\ListMedia;
use App\Models\Media;
use App\Models\User;
use App\Support\MediaCacheWarmer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

function makeMedia(array $attributes = []): Media
{
    return Media::query()->create(array_merge([
        'disk' => 'public',
        'directory' => '',
        'visibility' => 'public',
        'name' => 'voorbeeld',
        'path' => 'voorbeeld.mp4',
        'ext' => 'mp4',
        'type' => 'video/mp4',
        'size' => 1024,
        'title' => 'Voorbeeldvideo',
    ], $attributes));
}

it('renders the media library with the custom table', function () {
    makeMedia();

    Livewire::test(ListMedia::class)
        ->assertOk()
        ->assertCanSeeTableRecords(Media::query()->get());
});

it('can search media by file name', function () {
    $match = makeMedia(['name' => 'panda-video', 'path' => 'panda-video.mp4', 'title' => null]);
    $other = makeMedia(['name' => 'iets-anders', 'path' => 'iets-anders.mp4', 'title' => null]);

    Livewire::test(ListMedia::class)
        ->searchTable('panda')
        ->assertCanSeeTableRecords([$match])
        ->assertCanNotSeeTableRecords([$other]);
});

it('can filter media by type', function () {
    $video = makeMedia();
    $image = makeMedia(['name' => 'foto', 'path' => 'foto.jpg', 'ext' => 'jpg', 'type' => 'image/jpeg']);

    Livewire::test(ListMedia::class)
        ->filterTable('type', 'video')
        ->assertCanSeeTableRecords([$video])
        ->assertCanNotSeeTableRecords([$image]);
});

it('does not try to generate thumbnails for videos', function () {
    $video = makeMedia();

    expect(MediaCacheWarmer::warm($video))->toBeFalse();
});
