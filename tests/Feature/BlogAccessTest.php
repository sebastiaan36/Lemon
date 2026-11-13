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

it('passes the category and a short Dutch publish date to the blog index', function () {
    Blog::create([
        'title' => 'Categorised blog',
        'slug' => 'categorised-blog',
        'category' => 'Branding',
        'status' => CaseStatus::Published,
        'published_at' => '2026-10-01 10:00:00',
    ]);

    $this->get('/blog')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('blog/Index')
            ->where('blogs.0.category', 'Branding')
            ->where('blogs.0.publishedAt', '1 okt 2026'));
});

it('falls back to the creation date when a blog has no publish date', function () {
    $this->travelTo('2026-06-03 10:00:00');

    Blog::create([
        'title' => 'Undated blog',
        'slug' => 'undated-blog',
        'status' => CaseStatus::Published,
    ]);

    $this->get('/blog')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('blogs.0.publishedAt', '3 jun 2026'));
});

it('passes intro, labels, author and more articles to the blog page', function () {
    $blog = Blog::create([
        'title' => 'Main blog',
        'slug' => 'main-blog',
        'category' => 'Credible creativity',
        'tags' => ['Aviation', 'credible creativity', ' '],
        'author_name' => 'Kirsten <b>Markusse</b>',
        'intro' => 'Intro text',
        'content' => '<p>Body</p><blockquote><p>Quote</p></blockquote>',
        'status' => CaseStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    Blog::create([
        'title' => 'Other blog',
        'slug' => 'other-blog',
        'status' => CaseStatus::Published,
        'published_at' => now()->subDays(2),
    ]);

    Blog::create([
        'title' => 'Hidden concept',
        'slug' => 'hidden-concept',
        'status' => CaseStatus::Concept,
    ]);

    $this->get(route('blog.show', $blog))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('blog/Show')
            ->where('blog.intro', 'Intro text')
            ->where('blog.tags', ['Credible creativity', 'Aviation'])
            ->where('blog.author.name', 'Kirsten <b>Markusse</b>')
            ->where('blog.content', fn (string $html) => str_contains($html, '<footer class="blog-quote-author"><span>Kirsten &lt;b&gt;Markusse&lt;/b&gt;</span></footer></blockquote>'))
            ->has('moreBlogs', 1)
            ->where('moreBlogs.0.slug', 'other-blog'));
});
