<?php

namespace App\Http\Middleware;

use App\Models\CaseStudy;
use App\Models\FloatingPill;
use App\Models\Industry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'siteHeader' => fn (): array => [
                'highlightedCase' => $this->highlightedCase(),
                'industries' => $this->industries(),
            ],
            'floatingPills' => fn (): array => FloatingPill::forSite(),
        ];
    }

    /**
     * A random published, featured case with a homepage photo for the menu
     * overlay. Candidates are cached briefly so the header adds no queries
     * to most requests.
     *
     * @return array{name: string, slug: string, photo: ?string, video: ?string}|null
     */
    private function highlightedCase(): ?array
    {
        /** @var array<int, array{name: string, slug: string, photo: ?string, video: ?string}> $candidates */
        $candidates = Cache::remember('site-header.highlighted-cases', now()->addMinutes(10), function (): array {
            $cases = CaseStudy::featured()->whereNotNull('homepage_photo_id')->get();

            primeMedia($cases->pluck('homepage_photo_id')->all(), $cases->pluck('homepage_video_id')->all());

            return $cases
                ->map(fn (CaseStudy $case): array => [
                    'name' => $case->name,
                    'slug' => $case->slug,
                    'photo' => mediaUrl($case->homepage_photo_id),
                    'video' => mediaUrl($case->homepage_video_id),
                ])
                ->filter(fn (array $case): bool => filled($case['photo']))
                ->values()
                ->all();
        });

        return $candidates === [] ? null : $candidates[array_rand($candidates)];
    }

    /**
     * Gepubliceerde branches voor de Industries-kolom in het menu. Kort gecachet;
     * Industry wist de cache bij opslaan en verwijderen.
     *
     * @return list<array{name: string, slug: string}>
     */
    private function industries(): array
    {
        return Cache::remember(Industry::MENU_CACHE_KEY, now()->addMinutes(10), fn (): array => Industry::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['name', 'slug'])
            ->map(fn (Industry $industry): array => ['name' => $industry->name, 'slug' => $industry->slug])
            ->all());
    }
}
