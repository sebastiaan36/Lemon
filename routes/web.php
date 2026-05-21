<?php

use App\Models\AboutPageContent;
use App\Models\CaseStudy;
use App\Models\ContactPageContent;
use App\Models\CxbyExPageContent;
use App\Models\HomepageContent;
use App\Models\Job;
use App\Models\JobsPageContent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

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

Route::get('/contact', function () {
    $content = ContactPageContent::getSingleton();

    $teamMembers = collect($content->team_members ?? [])->map(function (array $member): array {
        return [
            'name' => $member['name'] ?? '',
            'role' => $member['role'] ?? '',
            'phone' => $member['phone'] ?? null,
            'email' => $member['email'] ?? null,
            'photo' => mediaUrl($member['photo'] ?? null),
        ];
    })->all();

    return Inertia::render('Contact', [
        'seoTitle' => $content->seo_title,
        'metaDescription' => $content->meta_description,
        'heroTitle' => $content->hero_title,
        'heroBgImage' => mediaUrl($content->hero_bg_image),
        'heroAddress' => $content->hero_address,
        'heroPhone' => $content->hero_phone,
        'heroEmail' => $content->hero_email,
        'introText' => $content->intro_text,
        'teamMembers' => $teamMembers,
        'clientLogosLabel' => $content->client_logos_label,
        'clientLogos' => mediaUrls($content->client_logos),
        'joinIntroText' => $content->join_intro_text,
        'joinTitleLine1' => $content->join_title_line1,
        'joinTitleLine2' => $content->join_title_line2,
        'joinJobs' => Job::published()->orderBy('sort_order')->limit(5)->get()
            ->map(fn (Job $job): array => ['title' => $job->title, 'slug' => $job->slug])
            ->all(),
        'joinButtonText' => $content->join_button_text,
    ]);
})->name('contact');

Route::get('/cxbyex', function () {
    $content = CxbyExPageContent::getSingleton();

    $brandLogos = collect($content->brand_logos ?? [])->map(function (array $item): array {
        return [
            'logo' => mediaUrl($item['logo'] ?? null),
            'name' => $item['name'] ?? '',
        ];
    })->filter(fn (array $item): bool => filled($item['logo']))->values()->all();

    return Inertia::render('CxbyEx', [
        'seoTitle' => $content->seo_title,
        'metaDescription' => $content->meta_description,
        'heroBgImage' => mediaUrl($content->hero_bg_image),
        'heroSubtitle' => $content->hero_subtitle,
        'narrativeText' => $content->narrative_text,
        'caseBgImage' => mediaUrl($content->case_bg_image),
        'caseBodyText' => $content->case_body_text,
        'caseClientName' => $content->case_client_name,
        'caseTags' => $content->case_tags ?? [],
        'bodyCol1' => $content->body_col1,
        'bodyCol2' => $content->body_col2,
        'quoteBgImage' => mediaUrl($content->quote_bg_image),
        'quoteText' => $content->quote_text,
        'quoteAuthor' => $content->quote_author,
        'steps' => $content->steps ?? [],
        'checklistImage' => mediaUrl($content->checklist_image),
        'checklistButtonText' => $content->checklist_button_text,
        'checklistHref' => $content->checklist_href,
        'brandsTitleLine1' => $content->brands_title_line1,
        'brandsTitleLine2' => $content->brands_title_line2,
        'brandLogos' => $brandLogos,
    ]);
})->name('cxbyex');

Route::get('/jobs', function () {
    $pageContent = JobsPageContent::getSingleton();

    $jobs = Job::published()
        ->orderBy('sort_order')
        ->get()
        ->map(fn (Job $job): array => [
            'slug' => $job->slug,
            'title' => $job->title,
            'jobTitle' => $job->job_title ?: $job->title,
            'shortDescription' => $job->short_description,
            'body' => $job->body,
            'tags' => $job->tags ?? [],
            'applyButtonText' => $job->apply_button_text,
            'applyEmail' => $job->apply_email,
            'applyContactName' => $job->apply_contact_name,
            'applyContactPhoto' => mediaUrl($job->apply_contact_photo),
        ])
        ->all();

    return Inertia::render('Jobs', [
        'seoTitle' => $pageContent->seo_title,
        'metaDescription' => $pageContent->meta_description,
        'heroBgImage' => mediaUrl($pageContent->hero_bg_image),
        'heroTitle' => $pageContent->hero_title,
        'heroSubtitle' => $pageContent->hero_subtitle,
        'jobs' => $jobs,
    ]);
})->name('jobs');

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
            'autoplayVideo' => (bool) $case->autoplay_video,
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
