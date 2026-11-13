<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Support\RichContent;
use Illuminate\Support\Str;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(): Response
    {
        $blogs = Blog::published()->latest('published_at')->latest()->get();

        primeMedia($blogs->pluck('header_photo')->all());

        return $this->page('blog/Index', [
            'blogs' => $blogs->map(fn (Blog $blog): array => [
                'title' => $blog->title,
                'slug' => $blog->slug,
                'headerPhoto' => mediaUrl($blog->header_photo),
                'excerpt' => Str::limit(trim(html_entity_decode(strip_tags((string) $blog->content))), 180),
                'publishedAt' => $blog->published_at?->format('d-m-Y'),
            ])->all(),
        ], 'Blog — Lemon Scented Tea');
    }

    public function show(Blog $blog): Response
    {
        $isPublished = Blog::published()->whereKey($blog->getKey())->exists();

        abort_unless($isPublished || $this->canViewConcepts(), 404);

        $headerPhoto = mediaUrl($blog->header_photo);

        return $this->page('blog/Show', [
            'blog' => [
                'title' => $blog->title,
                'headerPhoto' => $headerPhoto,
                'content' => RichContent::toSafeHtml($blog->content),
                'publishedAt' => $blog->published_at?->format('d-m-Y'),
                'seoTitle' => $blog->seo_title,
                'metaDescription' => $blog->meta_description,
            ],
        ], $blog->seo_title ?: $blog->title, $blog->meta_description, $headerPhoto);
    }
}
