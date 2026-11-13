<?php

use App\Enums\CaseStatus;
use App\Models\ActivityLog;
use App\Models\Blog;
use App\Models\CaseStudy;
use App\Models\HomepageContent;
use App\Models\Job;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(fn () => Storage::fake('public'));

function makeImage(string $name): Media
{
    Storage::disk('public')->put("{$name}.jpg", 'jpg');

    return Media::query()->create([
        'disk' => 'public',
        'directory' => '',
        'visibility' => 'public',
        'name' => $name,
        'path' => "{$name}.jpg",
        'ext' => 'jpg',
        'type' => 'image/jpeg',
        'size' => 3,
    ]);
}

it('keeps the slug when the title of a published item changes', function () {
    $blog = Blog::query()->create(['title' => 'Eerste titel', 'content' => '<p>x</p>', 'status' => CaseStatus::Published]);
    $job = Job::query()->create(['title' => 'Designer', 'status' => CaseStatus::Published]);
    $case = CaseStudy::factory()->published()->create(['name' => 'Panda']);

    $originalCaseSlug = $case->slug;

    $blog->update(['title' => 'Verbeterde titel']);
    $job->update(['title' => 'Senior designer']);
    $case->update(['name' => 'Panda verbeterd']);

    expect($blog->fresh()->slug)->toBe('eerste-titel')
        ->and($job->fresh()->slug)->toBe('designer')
        ->and($case->fresh()->slug)->toBe($originalCaseSlug);
});

it('normalises and de-duplicates an edited slug', function () {
    Blog::query()->create(['title' => 'Een', 'slug' => 'bestaand', 'content' => '<p>x</p>', 'status' => CaseStatus::Concept]);
    $blog = Blog::query()->create(['title' => 'Twee', 'content' => '<p>x</p>', 'status' => CaseStatus::Concept]);

    $blog->update(['slug' => 'Bestaand']);

    expect($blog->fresh()->slug)->toBe('bestaand-1');
});

it('resolves all homepage media in a single query', function () {
    Bus::withoutDispatchingAfterResponses();

    $images = collect(range(1, 6))->map(fn (int $i): Media => makeImage("foto-{$i}"));

    HomepageContent::getSingleton()->update([
        'hero_bg_image' => $images[0]->id,
        'hero_floating_images' => [$images[1]->id, $images[2]->id],
        'team_avatars' => [$images[3]->id],
        'team_photos' => [$images[4]->id, $images[5]->id],
    ]);

    DB::enableQueryLog();
    $this->get('/')->assertOk();

    $mediaQueries = collect(DB::getQueryLog())->filter(fn (array $query): bool => str_contains($query['query'], '"curator"') || str_contains($query['query'], '`curator`'));

    expect($mediaQueries)->toHaveCount(1);
});

it('rewrites media urls to the cdn when configured', function () {
    config(['services.bunny.cdn_url' => 'https://cdn.example.com/']);

    $image = makeImage('cdn-foto');

    expect(mediaUrl($image->id))->toStartWith('https://cdn.example.com/')
        ->and(publicStorageUrl('homepage/services/video.mp4'))->toBe('https://cdn.example.com/storage/homepage/services/video.mp4');
});

it('masks two-factor secrets in the activity log', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $admin->forceFill(['two_factor_secret' => encrypt('geheim')])->save();

    $log = ActivityLog::query()->latest('id')->first();

    expect($log->changes['two_factor_secret']['new'] ?? null)->toBe('••••••••');
});
