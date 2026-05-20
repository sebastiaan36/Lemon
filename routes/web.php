<?php

use App\Models\AboutPageContent;
use App\Models\CaseStudy;
use App\Models\HomepageContent;
use App\Models\Media;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

if (! function_exists('mediaUrl')) {
    /**
     * Resolve the CDN URL for a Curator media item (UUID or ID string).
     * If BUNNY_CDN_URL is configured the origin path is rewritten to the
     * Bunny pull-zone hostname; otherwise the storage URL is returned as-is.
     */
    function mediaUrl(?string $id): ?string
    {
        if (blank($id)) {
            return null;
        }

        $media = Media::find($id);
        if (! $media) {
            return null;
        }

        $url = $media->url;
        $cdnBase = config('services.bunny.cdn_url');

        if ($cdnBase && $url) {
            $path = parse_url($url, PHP_URL_PATH);

            return rtrim($cdnBase, '/').($path ?? '');
        }

        return $url;
    }
}

if (! function_exists('mediaUrls')) {
    /**
     * Map an array of Curator media IDs to URL strings.
     *
     * @param  array<mixed>|null  $ids
     * @return array<string>
     */
    function mediaUrls(?array $ids): array
    {
        if (blank($ids)) {
            return [];
        }

        return collect($ids)
            ->map(function ($id): ?string {
                // Curator may store items as full objects; extract the id field if so.
                if (is_array($id)) {
                    $id = $id['id'] ?? null;
                }

                return $id ? mediaUrl((string) $id) : null;
            })
            ->filter()
            ->values()
            ->all();
    }
}

if (! function_exists('mediaItem')) {
    /**
     * Resolve a Curator media item into a richer descriptor (url, type, width, height)
     * so the frontend can render images/videos with their natural aspect ratio.
     *
     * @return array{url: string, type: string, width: ?int, height: ?int}|null
     */
    function mediaItem(?string $id): ?array
    {
        if (blank($id)) {
            return null;
        }

        $media = Media::find($id);
        if (! $media) {
            return null;
        }

        $url = mediaUrl($id);
        if (blank($url)) {
            return null;
        }

        $type = str_starts_with((string) $media->type, 'video') ? 'video' : 'image';

        return [
            'url' => $url,
            'type' => $type,
            'width' => $media->width ? (int) $media->width : null,
            'height' => $media->height ? (int) $media->height : null,
        ];
    }
}

if (! function_exists('mediaItems')) {
    /**
     * Map an array of Curator media IDs to media-item descriptors.
     *
     * @param  array<mixed>|null  $ids
     * @return array<int, array{url: string, type: string, width: ?int, height: ?int}>
     */
    function mediaItems(?array $ids): array
    {
        if (blank($ids)) {
            return [];
        }

        return collect($ids)
            ->map(function ($id): ?array {
                if (is_array($id)) {
                    $id = $id['id'] ?? null;
                }

                return $id ? mediaItem((string) $id) : null;
            })
            ->filter()
            ->values()
            ->all();
    }
}

/**
 * Shared renderer for the Figma-based homepage so it can be exposed on
 * multiple routes without duplicating the large prop-mapping block.
 */
$renderHomepage = function () {
    $content = HomepageContent::getSingleton();

    // Services Repeater still uses FileUpload (public disk path)
    $publicUrl = fn (?string $path): ?string => $path ? asset('storage/'.$path) : null;

    $resolveServiceMedia = function (mixed $value) use ($publicUrl): ?string {
        if (is_array($value)) {
            $value = array_values($value)[0] ?? null;
        }

        return $publicUrl(is_string($value) ? $value : null);
    };

    $services = collect($content->services ?? [])->map(function (array $service) use ($resolveServiceMedia): array {
        $mediaUrl = $resolveServiceMedia($service['mediaUrl'] ?? null);
        $mediaType = $service['mediaType'] ?? 'image';

        if ($mediaUrl && $mediaType === 'image') {
            $ext = strtolower(pathinfo(parse_url($mediaUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
            if (in_array($ext, ['mp4', 'webm', 'mov', 'avi'])) {
                $mediaType = 'video';
            }
        }

        return array_merge($service, ['mediaUrl' => $mediaUrl, 'mediaType' => $mediaType]);
    })->all();

    $cases = CaseStudy::featured()->get()->map(fn (CaseStudy $case): array => [
        'name' => $case->name,
        'slug' => $case->slug,
        'video' => mediaUrl($case->homepage_video_id),
        'photo' => mediaUrl($case->homepage_photo_id),
    ])->all();

    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
        'seoTitle' => $content->seo_title,
        'metaDescription' => $content->meta_description,
        'heroTitle' => $content->hero_title,
        'heroBodyText' => $content->hero_body_text,
        'heroBgImage' => mediaUrl($content->hero_bg_image),
        'heroFloatingImages' => mediaUrls($content->hero_floating_images),
        'servicesIntroText' => $content->services_intro_text,
        'services' => $services,
        'teamAvatars' => mediaUrls($content->team_avatars),
        'cases' => $cases,
        'testimonialQuote' => $content->testimonial_quote,
        'testimonialAuthor' => $content->testimonial_author,
        'testimonialAuthorAvatar' => mediaUrl($content->testimonial_author_avatar),
        'testimonialLogo' => mediaUrl($content->testimonial_logo),
        'clientLogos' => mediaUrls($content->client_logos),
        'teamDescription' => $content->team_description,
        'teamButtonText' => $content->team_button_text,
        'teamButtonHref' => $content->team_button_href,
        'teamPhotos' => mediaUrls($content->team_photos),
    ]);
};

Route::get('/', $renderHomepage)->name('home');
Route::get('/home2', $renderHomepage)->name('home2');

Route::get('/about', function () {
    $content = AboutPageContent::getSingleton();

    $includeConcepts = Auth::check();

    $credibleCases = collect($content->credible_cases ?? [])
        ->map(fn ($id) => CaseStudy::find($id))
        ->filter()
        ->reject(fn (CaseStudy $case): bool => ! $includeConcepts && $case->isConcept())
        ->map(fn (CaseStudy $case): array => [
            'name' => $case->client_name ?: $case->name,
            'slug' => $case->slug,
            'video' => mediaUrl($case->homepage_video_id),
            'photo' => mediaUrl($case->homepage_photo_id),
            'touchpoints' => $case->touchpoints ?? [],
        ])
        ->values()
        ->all();

    return Inertia::render('About', [
        'seoTitle' => $content->seo_title,
        'metaDescription' => $content->meta_description,
        'heroTitle' => $content->hero_title,
        'heroDescription' => $content->hero_description,
        'introQuoteLine1' => $content->intro_quote_line1,
        'introQuoteLine2' => $content->intro_quote_line2,
        'introBgImage' => mediaItem($content->intro_bg_image),
        'ctaText' => $content->cta_text,
        'ctaHref' => $content->cta_href,
        'credibleTitleLine1' => $content->credible_title_line1,
        'credibleTitleLine2' => $content->credible_title_line2,
        'credibleBody' => $content->credible_body,
        'credibleCases' => $credibleCases,
        'growthTitleLine1' => $content->growth_title_line1,
        'growthTitleLine2' => $content->growth_title_line2,
        'growthBody' => $content->growth_body,
        'growthImage' => mediaItem($content->growth_image),
        'growthClientName' => $content->growth_client_name,
        'growthTags' => $content->growth_tags ?? [],
        'services' => $content->services ?? [],
        'ancientTitleLine1' => $content->ancient_title_line1,
        'ancientTitleLine2' => $content->ancient_title_line2,
        'ancientBody' => $content->ancient_body,
        'ancientImage' => mediaUrl($content->ancient_image),
        'internationalTitleLine1' => $content->international_title_line1,
        'internationalTitleLine2' => $content->international_title_line2,
        'internationalBody' => $content->international_body,
        'teamPhotos' => mediaUrls($content->team_photos),
        'joinTeamText' => $content->join_team_text,
        'joinTeamHref' => $content->join_team_href,
    ]);
})->name('about');

Route::get('/work', function () {
    $includeConcepts = Auth::check();

    $cases = CaseStudy::query()
        ->when(! $includeConcepts, fn ($q) => $q->published())
        ->orderBy('sort_order')
        ->get()
        ->map(fn (CaseStudy $case): array => [
            'name' => $case->client_name ?: $case->name,
            'slug' => $case->slug,
            'photo' => mediaUrl($case->homepage_photo_id),
            'video' => mediaUrl($case->homepage_video_id),
            'touchpoints' => $case->touchpoints ?? [],
        ])
        ->all();

    $allTouchpoints = collect($cases)
        ->flatMap(fn (array $c): array => $c['touchpoints'])
        ->unique()
        ->values()
        ->all();

    return Inertia::render('work/Index', [
        'cases' => $cases,
        'allTouchpoints' => $allTouchpoints,
    ]);
})->name('work.index');

Route::get('/work/{caseStudy:slug}', function (CaseStudy $caseStudy) {
    if ($caseStudy->isConcept() && ! Auth::check()) {
        abort(404);
    }

    $moreCases = CaseStudy::featured()
        ->whereKeyNot($caseStudy->getKey())
        ->limit(3)
        ->get()
        ->map(fn (CaseStudy $case): array => [
            'name' => $case->name,
            'slug' => $case->slug,
            'photo' => mediaUrl($case->homepage_photo_id),
            'video' => mediaUrl($case->homepage_video_id),
        ])->all();

    $galleryItems = mediaItems($caseStudy->gallery_items ?? []);

    $storyImages = collect($caseStudy->story_images ?? [])
        ->map(fn ($id): array => ['image' => mediaUrl((string) $id)])
        ->filter(fn (array $item): bool => filled($item['image']))
        ->values()
        ->all();

    return Inertia::render('work/Show', [
        'seoTitle' => $caseStudy->seo_title,
        'metaDescription' => $caseStudy->meta_description,
        'caseStudy' => [
            'name' => $caseStudy->name,
            'slug' => $caseStudy->slug,
            'clientName' => $caseStudy->client_name ?: $caseStudy->name,
            'accentColor' => $caseStudy->accent_color ?: '#0A7949',
            'heroTitle' => $caseStudy->hero_title ?: $caseStudy->name,
            'heroSubtitle' => $caseStudy->hero_subtitle ?: $caseStudy->client_name ?: $caseStudy->name,
            'heroMedia' => mediaUrl($caseStudy->hero_media),
            'heroDuration' => $caseStudy->hero_duration,
            'introLogo' => mediaUrl($caseStudy->intro_logo),
            'touchpoints' => $caseStudy->touchpoints ?? [],
            'overviewTitle' => $caseStudy->overview_title,
            'overviewBody' => $caseStudy->overview_body,
            'galleryItems' => $galleryItems,
            'highlightTitle' => $caseStudy->highlight_title,
            'highlightBody' => $caseStudy->highlight_body,
            'highlightMedia' => mediaUrl($caseStudy->highlight_media),
            'highlightButtonText' => $caseStudy->highlight_button_text,
            'campaignTitle' => $caseStudy->campaign_title,
            'campaignBody' => $caseStudy->campaign_body,
            'campaignMedia' => mediaUrl($caseStudy->campaign_media),
            'storyTitle' => $caseStudy->story_title,
            'storyBody' => $caseStudy->story_body,
            'storyMedia' => mediaUrl($caseStudy->story_media),
            'storyImages' => $storyImages,
            'calloutTitle' => $caseStudy->callout_title,
            'secondaryStoryMedia' => mediaUrl($caseStudy->secondary_story_media),
            'secondaryStoryBody' => $caseStudy->secondary_story_body,
            'secondaryStoryButtonText' => $caseStudy->secondary_story_button_text,
            'resultsHeading' => $caseStudy->results_heading ?: 'Results',
            'resultsStats' => $caseStudy->results_stats ?? [],
            'optionalPanelTitle' => $caseStudy->optional_panel_title,
            'optionalPanelBody' => $caseStudy->optional_panel_body,
            'optionalPanelButtonText' => $caseStudy->optional_panel_button_text,
        ],
        'moreCases' => $moreCases,
    ]);
})->name('work.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
