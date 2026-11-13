<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use Inertia\Response;

class CaseStudyController extends Controller
{
    public function index(): Response
    {
        $cases = CaseStudy::query()
            ->when(! $this->canViewConcepts(), fn ($query) => $query->published())
            ->orderBy('sort_order')
            ->get();

        primeMedia($cases->pluck('homepage_photo_id')->all(), $cases->pluck('homepage_video_id')->all());

        $mappedCases = $cases->map(fn (CaseStudy $case): array => [
            'name' => $case->client_name ?: $case->name,
            'slug' => $case->slug,
            'photo' => mediaUrl($case->homepage_photo_id),
            'video' => mediaUrl($case->homepage_video_id),
            'autoplayVideo' => (bool) $case->autoplay_video,
            'touchpoints' => $case->touchpoints ?? [],
        ])->all();

        return $this->page('cases/Index', [
            'cases' => $mappedCases,
            'allTouchpoints' => collect($mappedCases)
                ->flatMap(fn (array $case): array => $case['touchpoints'])
                ->unique()
                ->values()
                ->all(),
        ], 'Cases — Lemon Scented Tea');
    }

    public function show(CaseStudy $caseStudy): Response
    {
        abort_if($caseStudy->isConcept() && ! $this->canViewConcepts(), 404);

        $moreCases = CaseStudy::featured()
            ->whereKeyNot($caseStudy->getKey())
            ->limit(3)
            ->get();

        primeMedia(
            $caseStudy->hero_media,
            $caseStudy->intro_logo,
            $caseStudy->highlight_media,
            $caseStudy->campaign_media,
            $caseStudy->story_media,
            $caseStudy->secondary_story_media,
            $caseStudy->gallery_items ?? [],
            $caseStudy->pre_callout_gallery_items ?? [],
            $caseStudy->story_images ?? [],
            $moreCases->pluck('homepage_photo_id')->all(),
            $moreCases->pluck('homepage_video_id')->all(),
        );

        $heroMedia = mediaUrl($caseStudy->hero_media);

        $storyImages = collect($caseStudy->story_images ?? [])
            ->map(fn (mixed $id): array => ['image' => mediaUrl((string) $id)])
            ->filter(fn (array $item): bool => filled($item['image']))
            ->values()
            ->all();

        $galleryItems = mediaItems($caseStudy->gallery_items ?? []);
        $preCalloutGalleryItems = mediaItems($caseStudy->pre_callout_gallery_items ?? []);

        $moreCases = $moreCases->map(fn (CaseStudy $case): array => [
            'name' => $case->name,
            'slug' => $case->slug,
            'photo' => mediaUrl($case->homepage_photo_id),
            'video' => mediaUrl($case->homepage_video_id),
        ])->all();

        return $this->page('cases/Show', [
            'seoTitle' => $caseStudy->seo_title,
            'metaDescription' => $caseStudy->meta_description,
            'caseStudy' => [
                'name' => $caseStudy->name,
                'slug' => $caseStudy->slug,
                'clientName' => $caseStudy->client_name ?: $caseStudy->name,
                'accentColor' => $caseStudy->accent_color ?: '#0A7949',
                'styleVariant' => $caseStudy->style_variant ?: 1,
                'heroTitle' => $caseStudy->hero_title ?: $caseStudy->name,
                'heroTitleLines' => collect([
                    $caseStudy->hero_title_line_1,
                    $caseStudy->hero_title_line_2,
                    $caseStudy->hero_title_line_3,
                ])
                    ->filter(fn (?string $line): bool => filled($line))
                    ->values()
                    ->whenEmpty(fn ($lines) => $lines->push($caseStudy->hero_title ?: $caseStudy->name))
                    ->all(),
                'heroSubtitle' => $caseStudy->hero_subtitle ?: $caseStudy->client_name ?: $caseStudy->name,
                'heroMedia' => $heroMedia,
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
                'preCalloutTitle' => $caseStudy->pre_callout_title,
                'preCalloutBody' => $caseStudy->pre_callout_body,
                'preCalloutGalleryItems' => $preCalloutGalleryItems,
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
        ], $caseStudy->seo_title ?: $caseStudy->name, $caseStudy->meta_description, $heroMedia);
    }
}
