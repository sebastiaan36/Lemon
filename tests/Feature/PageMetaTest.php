<?php

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('falls back to the default meta description', function () {
    $this->get('/cases')
        ->assertOk()
        ->assertSee('<meta inertia="description" name="description" content="'.e(Controller::DEFAULT_DESCRIPTION).'">', false)
        ->assertInertia(fn ($page) => $page->where('pageDescription', Controller::DEFAULT_DESCRIPTION));
});

it('uses the blog intro when a blog has no meta description', function () {
    $blog = Blog::query()->create([
        'title' => 'Credible blog',
        'intro' => 'A short <strong>intro</strong> about credible creativity.',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $this->get("/blog/{$blog->slug}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('pageDescription', 'A short intro about credible creativity.'));
});

it('formats every tab title as "Page — Lemon Scented Tea"', function (string $url, string $title) {
    $this->get($url)
        ->assertOk()
        ->assertSee("<title inertia>{$title}</title>", false);
})->with([
    'jobs' => ['/jobs', 'Jobs — Lemon Scented Tea'],
    'cxbyex' => ['/cxbyex', 'CXbyEX — Lemon Scented Tea'],
    'contact' => ['/contact', 'Contact — Lemon Scented Tea'],
]);

it('adds the brand name to titles that lack it', function () {
    expect(Controller::formatTitle('Delta Air Lines'))->toBe('Delta Air Lines — Lemon Scented Tea')
        ->and(Controller::formatTitle('About — Lemon Scented Tea'))->toBe('About — Lemon Scented Tea')
        ->and(Controller::formatTitle(null))->toBe('Lemon Scented Tea');
});
