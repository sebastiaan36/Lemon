<?php

namespace App\Http\Controllers;

use App\Support\MediaResolver;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

abstract class Controller
{
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
    protected function page(string $component, array $props, ?string $title = null, ?string $description = null, ?string $image = null, bool $preloadImage = true, string $preloadSizes = '100vw'): Response
    {
        $media = app(MediaResolver::class);

        return Inertia::render($component, [
            ...$props,
            'mediaSources' => fn (): array => $media->manifest(),
        ])->withViewData([
            'meta' => [
                'title' => $title,
                'description' => $description,
                'image' => $media->shareImageFor($image),
                'preload' => $preloadImage ? $media->preloadFor($image) : null,
                'preloadSizes' => $preloadSizes,
            ],
        ]);
    }

    protected function canViewConcepts(): bool
    {
        return Gate::allows('view-concepts');
    }
}
