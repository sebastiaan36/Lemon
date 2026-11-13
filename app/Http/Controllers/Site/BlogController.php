<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Support\RichContent;
use Illuminate\Support\Str;
use Inertia\Response;

class BlogController extends Controller
{
    private const MORE_ARTICLES_LIMIT = 6;

    public function index(): Response
    {
        $blogs = Blog::published()->latest('published_at')->latest()->get();

        primeMedia($blogs->pluck('header_photo')->all());

        return $this->page('blog/Index', [
            'blogs' => $blogs->map(fn (Blog $blog): array => [
                ...$this->card($blog),
                'excerpt' => Str::limit(trim(html_entity_decode(strip_tags((string) $blog->content))), 180),
            ])->all(),
        ], 'Blog — Lemon Scented Tea');
    }

    public function show(Blog $blog): Response
    {
        $isPublished = Blog::published()->whereKey($blog->getKey())->exists();

        abort_unless($isPublished || $this->canViewConcepts(), 404);

        $moreBlogs = Blog::published()
            ->whereKeyNot($blog->getKey())
            ->latest('published_at')
            ->latest()
            ->limit(self::MORE_ARTICLES_LIMIT)
            ->get();

        primeMedia([$blog->header_photo, $blog->author_photo, ...$moreBlogs->pluck('header_photo')->all()]);

        $headerPhoto = mediaUrl($blog->header_photo);
        $authorPhoto = mediaUrl($blog->author_photo);

        return $this->page('blog/Show', [
            'blog' => [
                'title' => $blog->title,
                'headerPhoto' => $headerPhoto,
                'intro' => $blog->intro,
                'tags' => $this->labels($blog),
                'author' => $blog->author_name ? [
                    'name' => $blog->author_name,
                    'photo' => $authorPhoto,
                ] : null,
                'content' => $this->withQuoteAuthor(RichContent::toSafeHtml($blog->content), $blog->author_name, $authorPhoto),
                'publishedAt' => $this->shortDutchDate($blog),
                'seoTitle' => $blog->seo_title,
                'metaDescription' => $blog->meta_description,
            ],
            'moreBlogs' => $moreBlogs->map(fn (Blog $other): array => $this->card($other))->all(),
        ], $blog->seo_title ?: $blog->title, $blog->meta_description, $headerPhoto, preloadSizes: '(min-width: 1024px) 47.5vw, 100vw');
    }

    /**
     * @return array{title: string, slug: string, category: ?string, headerPhoto: ?string, publishedAt: ?string}
     */
    private function card(Blog $blog): array
    {
        return [
            'title' => $blog->title,
            'slug' => $blog->slug,
            'category' => $blog->category,
            'headerPhoto' => mediaUrl($blog->header_photo),
            'publishedAt' => $this->shortDutchDate($blog),
        ];
    }

    /**
     * Categorie gevolgd door de losse tags, zonder lege of dubbele waarden.
     *
     * @return list<string>
     */
    private function labels(Blog $blog): array
    {
        return collect([$blog->category, ...($blog->tags ?? [])])
            ->map(fn (mixed $label): string => trim((string) $label))
            ->filter()
            ->unique(fn (string $label): string => Str::lower($label))
            ->values()
            ->all();
    }

    /**
     * Figma: een citaat in de blog is een geel kaartje met de auteur eronder.
     * De auteur wordt na het saneren toegevoegd; naam en URL worden ge-escaped.
     */
    private function withQuoteAuthor(string $html, ?string $authorName, ?string $authorPhoto): string
    {
        if (blank($authorName) || ! str_contains($html, '</blockquote>')) {
            return $html;
        }

        $photo = $authorPhoto
            ? '<img class="blog-quote-avatar" src="'.e($authorPhoto).'" alt="" width="38" height="38">'
            : '';

        return str_replace(
            '</blockquote>',
            '<footer class="blog-quote-author">'.$photo.'<span>'.e($authorName).'</span></footer></blockquote>',
            $html,
        );
    }

    /**
     * Korte Nederlandse datum zoals in het ontwerp ("1 okt 2026"). Valt terug op
     * de aanmaakdatum wanneer er geen publicatiedatum is ingevuld.
     */
    private function shortDutchDate(Blog $blog): ?string
    {
        $date = $blog->published_at ?? $blog->created_at;

        return $date ? str_replace('.', '', $date->locale('nl')->translatedFormat('j M Y')) : null;
    }
}
