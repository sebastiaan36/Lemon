<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\AboutPageContent;
use App\Models\CaseStudy;
use Inertia\Response;

class AboutController extends Controller
{
    public function __invoke(): Response
    {
        $content = AboutPageContent::getSingleton();

        $selectedIds = collect($content->credible_cases ?? [])->filter()->map(fn (mixed $id): string => (string) $id)->values();

        $cases = CaseStudy::query()
            ->whereKey($selectedIds->all())
            ->when(! $this->canViewConcepts(), fn ($query) => $query->published())
            ->get()
            ->keyBy(fn (CaseStudy $case): string => (string) $case->getKey());

        primeMedia(
            $content->intro_bg_image,
            $content->growth_image,
            $content->ancient_image,
            $content->team_photos,
            $cases->pluck('homepage_photo_id')->all(),
            $cases->pluck('homepage_video_id')->all(),
        );

        $credibleCases = $selectedIds
            ->map(fn (string $id): ?CaseStudy => $cases->get($id))
            ->filter()
            ->map(fn (CaseStudy $case): array => [
                'name' => $case->client_name ?: $case->name,
                'slug' => $case->slug,
                'video' => mediaUrl($case->homepage_video_id),
                'photo' => mediaUrl($case->homepage_photo_id),
                'touchpoints' => $case->touchpoints ?? [],
            ])
            ->values()
            ->all();

        $introBgImage = mediaItem($content->intro_bg_image);

        return $this->page('About', [
            'seoTitle' => $content->seo_title,
            'metaDescription' => $content->meta_description,
            'heroTitle' => $content->hero_title,
            'heroDescription' => $content->hero_description,
            'introQuoteLine1' => $content->intro_quote_line1,
            'introQuoteLine2' => $content->intro_quote_line2,
            'introBgImage' => $introBgImage,
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
        ], $content->seo_title ?? 'About — Lemon Scented Tea', $content->meta_description, $introBgImage['url'] ?? null);
    }
}
