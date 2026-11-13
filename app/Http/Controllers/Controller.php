<?php

namespace App\Http\Controllers;

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
     * @param  array<string, mixed>  $props
     */
    protected function page(string $component, array $props, ?string $title = null, ?string $description = null, ?string $image = null): Response
    {
        return Inertia::render($component, $props)->withViewData([
            'meta' => [
                'title' => $title,
                'description' => $description,
                'image' => $image,
            ],
        ]);
    }

    protected function canViewConcepts(): bool
    {
        return Gate::allows('view-concepts');
    }
}
