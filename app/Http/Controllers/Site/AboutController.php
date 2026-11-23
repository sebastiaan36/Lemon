<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\AboutPageContent;
use Inertia\Response;

class AboutController extends Controller
{
    public function __invoke(): Response
    {
        $content = AboutPageContent::getSingleton();

        primeMedia(
            $content->intro_bg_image,
            $content->growth_image,
            $content->ancient_image,
            $content->narrative_image,
            $content->team_photos,
        );

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
            'growthTitleLine1' => $content->growth_title_line1,
            'growthTitleLine2' => $content->growth_title_line2,
            'growthBody' => $content->growth_body,
            'growthImage' => mediaItem($content->growth_image),
            'growthClientName' => $content->growth_client_name,
            'growthTags' => $content->growth_tags ?? [],
            'dimensionsTitle' => $content->dimensions_title,
            'dimensionsBody' => $content->dimensions_body,
            'dimensionsFormulaTitle' => $content->dimensions_formula_title,
            'dimensionsFormulaSubtitle' => $content->dimensions_formula_subtitle,
            'dimensionsCenterTitle' => $content->dimensions_center_title,
            'dimensionsCenterSubtitle' => $content->dimensions_center_subtitle,
            'dimensionsItems' => array_values($content->dimensions_items ?? []),
            'services' => $content->services ?? [],
            'ancientTitleLine1' => $content->ancient_title_line1,
            'ancientTitleLine2' => $content->ancient_title_line2,
            'ancientBody' => $content->ancient_body,
            'ancientImage' => mediaUrl($content->ancient_image),
            'approachTitle' => $content->approach_title,
            'approachBody' => $content->approach_body,
            'approachButtonText' => $content->approach_button_text,
            'approachButtonHref' => $content->approach_button_href,
            'narrativeTitle' => $content->narrative_title,
            'narrativeBody' => $content->narrative_body,
            'narrativeButtonText' => $content->narrative_button_text,
            'narrativeButtonHref' => $content->narrative_button_href,
            'narrativeImage' => mediaItem($content->narrative_image),
            'narrativeClientName' => $content->narrative_client_name,
            'narrativeTags' => $content->narrative_tags ?? [],
            'internationalTitleLine1' => $content->international_title_line1,
            'internationalTitleLine2' => $content->international_title_line2,
            'internationalBody' => $content->international_body,
            'teamPhotos' => mediaUrls($content->team_photos),
            'joinTeamText' => $content->join_team_text,
            'joinTeamHref' => $content->join_team_href,
        ], $content->seo_title ?? 'About — Lemon Scented Tea', $content->meta_description, $introBgImage['url'] ?? null, preloadImage: false);
    }
}
