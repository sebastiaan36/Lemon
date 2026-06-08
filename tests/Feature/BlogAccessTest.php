<?php

use App\Enums\CaseStatus;
use App\Models\Blog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows published blogs on the blog index', function () {
    Blog::create([
        'title' => 'Published blog',
        'slug' => 'published-blog',
        'content' => '<p>Published content</p>',
        'status' => CaseStatus::Published,
        'published_at' => now(),
    ]);

    Blog::create([
        'title' => 'Concept blog',
        'slug' => 'concept-blog',
        'content' => '<p>Concept content</p>',
        'status' => CaseStatus::Concept,
    ]);

    $this->get('/blog')
        ->assertOk()
        ->assertSee('Published blog')
        ->assertDontSee('Concept blog');
});

it('allows guests to view a published blog', function () {
    $blog = Blog::create([
        'title' => 'Published blog',
        'slug' => 'published-blog',
        'content' => '<p>Published content</p>',
        'status' => CaseStatus::Published,
        'published_at' => now(),
    ]);

    $this->get(route('blog.show', $blog))->assertOk();
});

it('returns 404 for guests viewing a concept blog', function () {
    $blog = Blog::create([
        'title' => 'Concept blog',
        'slug' => 'concept-blog',
        'content' => '<p>Concept content</p>',
        'status' => CaseStatus::Concept,
    ]);

    $this->get(route('blog.show', $blog))->assertNotFound();
});

it('allows authenticated users to preview a concept blog', function () {
    $blog = Blog::create([
        'title' => 'Concept blog',
        'slug' => 'concept-blog',
        'content' => '<p>Concept content</p>',
        'status' => CaseStatus::Concept,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('blog.show', $blog))
        ->assertOk();
});
