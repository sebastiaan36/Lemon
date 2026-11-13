<?php

use App\Enums\CaseStatus;
use App\Models\AboutPageContent;
use App\Models\Blog;
use App\Models\CaseStudy;
use App\Models\Job;
use App\Models\Media;
use App\Support\SvgSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    Cache::flush();
});

function storeMedia(string $name, string $ext, string $type, string $contents): Media
{
    Storage::disk('public')->put("{$name}.{$ext}", $contents);

    return Media::query()->create([
        'disk' => 'public',
        'directory' => '',
        'visibility' => 'public',
        'name' => $name,
        'path' => "{$name}.{$ext}",
        'ext' => $ext,
        'type' => $type,
        'size' => strlen($contents),
    ]);
}

it('strips scripts and event handlers from job texts', function () {
    Job::query()->create([
        'title' => 'Designer',
        'body' => '<p>Kom werken</p><img src="x" onerror="alert(1)"><script>alert(2)</script><a href="javascript:alert(3)">klik</a>',
        'status' => CaseStatus::Published,
    ]);

    $this->get('/jobs')->assertInertia(fn (Assert $page) => $page
        ->where('jobs.0.body', fn (string $body): bool => str_contains($body, 'Kom werken')
            && ! str_contains($body, 'onerror')
            && ! str_contains($body, '<script')
            && ! str_contains($body, 'javascript:'))
    );
});

it('strips scripts from blog content', function () {
    $blog = Blog::query()->create([
        'title' => 'Veilige blog',
        'content' => '<p>Tekst</p><img src="x" onerror="alert(1)">',
        'status' => CaseStatus::Published,
        'published_at' => now(),
    ]);

    $this->get(route('blog.show', $blog))->assertInertia(fn (Assert $page) => $page
        ->where('blog.content', fn (string $content): bool => str_contains($content, 'Tekst') && ! str_contains($content, 'onerror'))
    );
});

it('hides concept cases from guests on the cases and about pages', function () {
    $published = CaseStudy::factory()->published()->create(['name' => 'Gepubliceerd']);
    $concept = CaseStudy::factory()->concept()->create(['name' => 'Geheim concept']);

    AboutPageContent::getSingleton()->update(['credible_cases' => [$concept->id, $published->id]]);

    $this->get('/cases')->assertInertia(fn (Assert $page) => $page->has('cases', 1)->where('cases.0.slug', $published->slug));
    $this->get('/about')
        ->assertInertia(fn (Assert $page) => $page->missing('credibleCases'))
        ->assertDontSee('Geheim concept');
});

it('never puts a concept case in the site header', function () {
    $photo = storeMedia('concept-foto', 'jpg', 'image/jpeg', 'jpg');

    CaseStudy::factory()->concept()->create(['is_featured' => true, 'homepage_photo_id' => $photo->id]);

    $this->get('/contact')->assertInertia(fn (Assert $page) => $page->where('siteHeader.highlightedCase', null));
});

it('highlights a published featured case in the site header', function () {
    $photo = storeMedia('case-foto', 'jpg', 'image/jpeg', 'jpg');

    $case = CaseStudy::factory()->published()->create(['is_featured' => true, 'homepage_photo_id' => $photo->id]);

    $this->get('/contact')->assertInertia(fn (Assert $page) => $page
        ->where('siteHeader.highlightedCase.slug', $case->slug)
        ->where('siteHeader.highlightedCase.photo', fn (string $url): bool => str_contains($url, 'case-foto'))
    );
});

it('sanitizes uploaded svg files in the media library', function () {
    $media = storeMedia('logo', 'svg', 'image/svg+xml', <<<'SVG'
        <svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)" viewBox="0 0 10 10">
            <script>alert(2)</script>
            <rect width="10" height="10" fill="#0A7949"/>
            <foreignObject><iframe src="https://evil.example"></iframe></foreignObject>
            <a href="javascript:alert(3)"><circle r="2"/></a>
        </svg>
        SVG);

    $stored = Storage::disk('public')->get($media->path);

    expect($stored)
        ->toContain('<rect')
        ->not->toContain('onload')
        ->not->toContain('<script')
        ->not->toContain('foreignObject')
        ->not->toContain('javascript:')
        ->and($media->size)->toBe(strlen($stored));
});

it('rejects svg files that cannot be parsed and removes them from disk', function () {
    $mediaCountBefore = Media::query()->count();

    expect(fn () => storeMedia('kapot', 'svg', 'image/svg+xml', '<svg><script>alert(1)'))
        ->toThrow(ValidationException::class);

    expect(Storage::disk('public')->exists('kapot.svg'))->toBeFalse()
        ->and(Media::query()->count())->toBe($mediaCountBefore);
});

it('rejects compressed svgz uploads', function () {
    expect(fn () => storeMedia('gecomprimeerd', 'svgz', 'image/svg+xml', gzencode('<svg/>')))
        ->toThrow(ValidationException::class);
});

it('detects svg by extension or mime type', function (?string $ext, ?string $mime, bool $expected) {
    expect(SvgSanitizer::isSvg($ext, $mime))->toBe($expected);
})->with([
    ['svg', null, true],
    ['SVG', null, true],
    ['png', 'image/svg+xml', true],
    ['png', 'image/png', false],
]);
