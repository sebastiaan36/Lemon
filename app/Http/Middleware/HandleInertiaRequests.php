<?php

namespace App\Http\Middleware;

use App\Models\CaseStudy;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $publicUrl = fn (?string $path): ?string => $path ? asset('storage/'.$path) : null;

        $highlightedCase = CaseStudy::featured()
            ->whereNotNull('photo')
            ->inRandomOrder()
            ->first()
            ?? CaseStudy::query()->whereNotNull('photo')->inRandomOrder()->first();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'siteHeader' => [
                'highlightedCase' => $highlightedCase ? [
                    'name' => $highlightedCase->name,
                    'slug' => $highlightedCase->slug,
                    'photo' => $publicUrl($highlightedCase->photo),
                    'video' => $publicUrl($highlightedCase->video),
                ] : null,
            ],
        ];
    }
}
