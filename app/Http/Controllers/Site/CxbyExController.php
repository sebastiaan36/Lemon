<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\CxbyExPageContent;
use Inertia\Response;

class CxbyExController extends Controller
{
    public function __invoke(): Response
    {
        $content = CxbyExPageContent::getSingleton();
        $brandLogos = collect($content->brand_logos ?? []);

        primeMedia(
            $content->hero_bg_image,
            $content->case_bg_image,
            $content->quote_bg_image,
            $content->checklist_image,
            $brandLogos->pluck('logo')->all(),
        );

        $heroBgImage = mediaUrl($content->hero_bg_image);

        return $this->page('CxbyEx', [
            'seoTitle' => $content->seo_title,
            'metaDescription' => $content->meta_description,
            'heroBgImage' => $heroBgImage,
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
            'brandLogos' => $brandLogos
                ->map(fn (array $item): array => [
                    'logo' => mediaUrl($item['logo'] ?? null),
                    'name' => $item['name'] ?? '',
                ])
                ->filter(fn (array $item): bool => filled($item['logo']))
                ->values()
                ->all(),
        ], $content->seo_title ?: 'CXbyEX', $content->meta_description, $heroBgImage);
    }
}
