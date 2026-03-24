<?php

use App\Models\CaseStudy;
use App\Models\HomepageContent;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

/**
 * Shared renderer for the Figma-based homepage so it can be exposed on
 * multiple routes without duplicating the large prop-mapping block.
 */
$renderHomepage = function () {
    $content = HomepageContent::getSingleton();

    $publicUrl = fn (?string $path): ?string => $path ? asset('storage/'.$path) : null;
    $caseSummary = fn (CaseStudy $case): array => [
        'name' => $case->name,
        'slug' => $case->slug,
        'video' => $publicUrl($case->video),
        'photo' => $publicUrl($case->photo),
    ];

    $floatingImages = collect($content->hero_floating_images ?? [])
        ->map(fn ($item) => $publicUrl($item['image'] ?? null))
        ->values()
        ->all();

    // mediaUrl kan een string of array zijn afhankelijk van hoe Filament het opslaat
    $resolveMedia = function (mixed $value) use ($publicUrl): ?string {
        if (is_array($value)) {
            $value = array_values($value)[0] ?? null;
        }

        return $publicUrl(is_string($value) ? $value : null);
    };

    $services = collect($content->services ?? [])->map(function (array $service) use ($resolveMedia): array {
        $mediaUrl = $resolveMedia($service['mediaUrl'] ?? null);
        $mediaType = $service['mediaType'] ?? 'image';

        // Auto-detecteer type op basis van bestandsextensie als het niet klopt
        if ($mediaUrl && $mediaType === 'image') {
            $ext = strtolower(pathinfo(parse_url($mediaUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
            if (in_array($ext, ['mp4', 'webm', 'mov', 'avi'])) {
                $mediaType = 'video';
            }
        }

        return array_merge($service, ['mediaUrl' => $mediaUrl, 'mediaType' => $mediaType]);
    })->all();

    $teamAvatars = collect($content->team_avatars ?? [])
        ->map(fn ($item) => $publicUrl($item['avatar'] ?? null))
        ->filter()
        ->values()
        ->all();

    $cases = CaseStudy::featured()->get()->map($caseSummary)->all();

    $clientLogos = collect($content->client_logos ?? [])
        ->map(fn ($item) => $publicUrl($item['logo'] ?? null))
        ->filter()
        ->values()
        ->all();

    $teamPhotos = collect($content->team_photos ?? [])
        ->map(fn ($item) => $publicUrl($item['photo'] ?? null))
        ->filter()
        ->values()
        ->all();

    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
        'heroTitle' => $content->hero_title,
        'heroBodyText' => $content->hero_body_text,
        'heroBgImage' => $publicUrl($content->hero_bg_image),
        'heroFloatingImages' => $floatingImages,
        'servicesIntroText' => $content->services_intro_text,
        'services' => $services,
        'teamAvatars' => $teamAvatars,
        'cases' => $cases,
        'testimonialQuote' => $content->testimonial_quote,
        'testimonialAuthor' => $content->testimonial_author,
        'testimonialAuthorAvatar' => $publicUrl($content->testimonial_author_avatar),
        'testimonialLogo' => $publicUrl($content->testimonial_logo),
        'clientLogos' => $clientLogos,
        'teamDescription' => $content->team_description,
        'teamButtonText' => $content->team_button_text,
        'teamButtonHref' => $content->team_button_href,
        'teamPhotos' => $teamPhotos,
    ]);
};

Route::get('/', $renderHomepage)->name('home');
Route::get('/home2', $renderHomepage)->name('home2');
Route::get('/cases/{caseStudy:slug}', function (CaseStudy $caseStudy) {
    $publicUrl = fn (?string $path): ?string => $path ? asset('storage/'.$path) : null;
    $mapVisualItems = fn (?array $items): array => collect($items ?? [])
        ->map(fn (array $item): array => [
            'image' => $publicUrl($item['image'] ?? null),
        ])
        ->filter(fn (array $item): bool => (bool) $item['image'])
        ->values()
        ->all();

    $moreCases = CaseStudy::featured()
        ->whereKeyNot($caseStudy->getKey())
        ->limit(3)
        ->get()
        ->map(fn (CaseStudy $case): array => [
            'name' => $case->name,
            'slug' => $case->slug,
            'photo' => $publicUrl($case->photo),
            'video' => $publicUrl($case->video),
        ])->all();

    return Inertia::render('cases/Show', [
        'caseStudy' => [
            'name' => $caseStudy->name,
            'slug' => $caseStudy->slug,
            'clientName' => $caseStudy->client_name ?: $caseStudy->name,
            'accentColor' => $caseStudy->accent_color ?: '#0A7949',
            'heroTitle' => $caseStudy->hero_title ?: $caseStudy->name,
            'heroSubtitle' => $caseStudy->hero_subtitle ?: $caseStudy->client_name ?: $caseStudy->name,
            'heroMedia' => $publicUrl($caseStudy->hero_media),
            'heroDuration' => $caseStudy->hero_duration,
            'introLogo' => $publicUrl($caseStudy->intro_logo),
            'touchpoints' => $caseStudy->touchpoints ?? [],
            'overviewTitle' => $caseStudy->overview_title,
            'overviewBody' => $caseStudy->overview_body,
            'galleryItems' => $mapVisualItems($caseStudy->gallery_items),
            'highlightTitle' => $caseStudy->highlight_title,
            'highlightBody' => $caseStudy->highlight_body,
            'highlightMedia' => $publicUrl($caseStudy->highlight_media),
            'highlightButtonText' => $caseStudy->highlight_button_text,
            'campaignTitle' => $caseStudy->campaign_title,
            'campaignBody' => $caseStudy->campaign_body,
            'campaignMedia' => $publicUrl($caseStudy->campaign_media),
            'storyTitle' => $caseStudy->story_title,
            'storyBody' => $caseStudy->story_body,
            'storyImages' => $mapVisualItems($caseStudy->story_images),
            'resultsHeading' => $caseStudy->results_heading ?: 'Results',
            'resultsStats' => $caseStudy->results_stats ?? [],
            'optionalPanelTitle' => $caseStudy->optional_panel_title,
            'optionalPanelBody' => $caseStudy->optional_panel_body,
            'optionalPanelButtonText' => $caseStudy->optional_panel_button_text,
        ],
        'moreCases' => $moreCases,
    ]);
})->name('cases.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
