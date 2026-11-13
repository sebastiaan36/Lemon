<?php

use App\Models\HomepageContent;
use App\Models\Media;
use App\Support\ImageVariants;
use App\Support\MediaResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    Bus::withoutDispatchingAfterResponses();
});

function storeTestJpeg(string $path = 'foto.jpg'): void
{
    $image = ImageManager::gd()->create(1200, 800)->fill('#e9c46a');
    Storage::disk('public')->put($path, (string) $image->toJpeg(quality: 100));
}

function makeImageMedia(string $name = 'foto'): Media
{
    storeTestJpeg("{$name}.jpg");

    return Media::query()->create([
        'disk' => 'public',
        'directory' => '',
        'visibility' => 'public',
        'name' => $name,
        'path' => "{$name}.jpg",
        'ext' => 'jpg',
        'type' => 'image/jpeg',
        'size' => Storage::disk('public')->size("{$name}.jpg"),
        'width' => 1200,
        'height' => 800,
    ]);
}

it('converts an uploaded image to the supported modern formats', function () {
    $media = makeImageMedia()->refresh();

    $variants = ImageVariants::paths($media);

    expect($variants)->toHaveKey('webp');
    expect(array_keys($variants))->toEqualCanonicalizing(ImageVariants::supportedFormats());

    foreach ($variants as $format => $path) {
        expect($path)->toEndWith(".{$format}");
        Storage::disk('public')->assertExists($path);
        expect(Storage::disk('public')->size($path))->toBeLessThan($media->size);
    }

    Storage::disk('public')->assertExists($media->path);
});

it('generates smaller widths for srcset below the full size', function () {
    $media = makeImageMedia()->refresh();

    expect(array_keys(ImageVariants::widths($media)['webp']))->toBe([640, 1200]);
    Storage::disk('public')->assertExists(str_replace('.jpg', '-640w.webp', $media->path));
});

it('hands the srcsets of used images to the page and preloads the hero', function () {
    $media = makeImageMedia()->refresh();
    $resolver = app(MediaResolver::class);

    $url = mediaUrl($media->id);
    $manifest = $resolver->manifest();

    expect($manifest)->toHaveKey($url)
        ->and($manifest[$url]['webp'])->toContain('-640w.webp 640w')
        ->and($manifest[$url]['webp'])->toContain('.webp 1200w')
        ->and($manifest[$url]['width'])->toBe(1200)
        ->and($manifest[$url]['height'])->toBe(800);

    expect($resolver->preloadFor($url))->toMatchArray(['href' => $url, 'type' => 'image/webp'])
        ->and($resolver->preloadFor('https://elders.example/x.jpg'))->toBeNull();
});

it('treats single-width variants from before srcset support as outdated', function () {
    $media = makeImageMedia()->refresh();
    expect(ImageVariants::needsConversion($media))->toBeFalse();

    $media->custom_properties = ['variants' => ['webp' => 'foto.webp']];

    expect(ImageVariants::needsConversion($media))->toBeTrue()
        ->and(ImageVariants::paths($media))->toBe(['webp' => 'foto.webp']);
});

it('serves the webp variant and maps it back to the original', function () {
    $media = makeImageMedia()->refresh();
    $webpPath = ImageVariants::paths($media)['webp'];

    expect(mediaUrl($media->id))->toEndWith($webpPath);

    $item = mediaItem($media->id);
    expect($item['url'])->toEndWith($webpPath);

    expect(app(MediaResolver::class)->originalUrlFor($item['url']))->toEndWith($media->path);
});

it('does not convert videos or svgs', function () {
    $video = Media::query()->create([
        'disk' => 'public', 'directory' => '', 'visibility' => 'public',
        'name' => 'clip', 'path' => 'clip.mp4', 'ext' => 'mp4', 'type' => 'video/mp4', 'size' => 10,
    ]);

    expect(ImageVariants::paths($video->refresh()))->toBe([])
        ->and(mediaUrl($video->id))->toEndWith('.mp4')
        ->and(app(MediaResolver::class)->manifest())->toBe([]);
});

it('removes the variants when the media is deleted', function () {
    $media = makeImageMedia()->refresh();
    $variants = ImageVariants::paths($media);

    $media->delete();

    foreach ($variants as $path) {
        Storage::disk('public')->assertMissing($path);
    }
});

it('regenerates variants when the file path changes', function () {
    $media = makeImageMedia()->refresh();
    $oldWebp = ImageVariants::paths($media)['webp'];

    storeTestJpeg('nieuw.jpg');
    $media->update(['path' => 'nieuw.jpg', 'name' => 'nieuw']);

    Storage::disk('public')->assertMissing($oldWebp);
    $media->refresh();
    expect(ImageVariants::paths($media)['webp'])->toBe(str_replace('.jpg', '.webp', $media->path));
});

it('converts existing images with the artisan command', function () {
    $media = makeImageMedia();
    $media->custom_properties = null;
    $media->saveQuietly();
    Storage::disk('public')->delete(Storage::disk('public')->files('', false));
    storeTestJpeg($media->path);

    $this->artisan('media:convert-images')->assertSuccessful();

    expect(ImageVariants::paths($media->refresh()))->toHaveKey('webp');
});

it('renders the srcset manifest and a hero preload on a public page', function () {
    $media = makeImageMedia()->refresh();
    HomepageContent::getSingleton()->update(['hero_bg_image' => $media->id]);

    $url = app(MediaResolver::class)->url($media->id);

    $this->get('/')
        ->assertOk()
        ->assertSee('rel="preload" as="image"', false)
        ->assertSee('-640w.webp 640w', false)
        ->assertInertia(fn ($page) => $page->where('mediaSources', fn ($sources): bool => isset($sources[$url]['webp'], $sources[$url]['width'])));
});
