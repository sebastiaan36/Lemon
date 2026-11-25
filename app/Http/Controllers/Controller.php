<?php

namespace App\Http\Controllers;

use App\Support\MediaResolver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

abstract class Controller
{
    /** Meta description voor pagina's zonder eigen tekst in het CMS. */
    public const SITE_NAME = 'Lemon Scented Tea';

    public const DEFAULT_DESCRIPTION = 'Lemon Scented Tea is a brand, CX and EX agency in Amsterdam. We turn the essence of your brand into Credible Creativity that people feel.';

    /**
     * Render an Inertia page and hand SEO metadata to the root Blade view, so
     * title, description, canonical and Open Graph tags are present in the
     * initial HTML even without server-side rendering.
     *
     * Every page also receives `mediaSources`: the responsive AVIF/WebP
     * sources of all CMS images used on it, for the <CmsImage> component.
     * It is resolved last, after all other props have called mediaUrl().
     * When `$preloadImage` is set, the meta image is the page's hero and is
     * preloaded in the <head>; `$preloadSizes` must match the `sizes` of that
     * hero's <CmsImage>, or the browser downloads two different candidates.
     *
     * @param  array<string, mixed>  $props
     */
    protected function page(string $component, array $props, ?string $title = null, ?string $description = null, ?string $image = null, bool $preloadImage = true, string $preloadSizes = '100vw', bool $noindex = false): Response
    {
        $media = app(MediaResolver::class);
        $description = filled($description)
            ? Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($description))), 160)
            : self::DEFAULT_DESCRIPTION;

        return Inertia::render($component, [
            ...$props,
            'pageDescription' => $description,
            'mediaSources' => fn (): array => $media->manifest(),
        ])->withViewData([
            'meta' => [
                'title' => self::formatTitle($title),
                'description' => $description,
                'image' => $media->shareImageFor($image),
                'preload' => $preloadImage ? $media->preloadFor($image) : null,
                'preloadSizes' => $preloadSizes,
                'noindex' => $noindex,
            ],
        ]);
    }

    /**
     * Every tab title reads "Page — Lemon Scented Tea"; titles that already
     * contain the brand name are kept as they are.
     */
    public static function formatTitle(?string $title): string
    {
        $title = trim((string) $title);

        if ($title === '') {
            return self::SITE_NAME;
        }

        return Str::contains($title, self::SITE_NAME) ? $title : $title.' — '.self::SITE_NAME;
    }

    protected function canViewConcepts(): bool
    {
        return Gate::allows('view-concepts');
    }
}
