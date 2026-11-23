<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use App\Models\IndustriesPageContent;
use App\Models\Industry;
use Illuminate\Support\Collection;
use Inertia\Response;

class IndustryController extends Controller
{
    public function __invoke(): Response
    {
        $content = IndustriesPageContent::getSingleton();

        $industries = Industry::query()
            ->when(! $this->canViewConcepts(), fn ($query) => $query->published())
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $caseIds = $industries
            ->flatMap(fn (Industry $industry): array => $industry->cases ?? [])
            ->filter()
            ->map(fn (mixed $id): string => (string) $id)
            ->unique()
            ->values();

        $cases = CaseStudy::query()
            ->whereKey($caseIds->all())
            ->when(! $this->canViewConcepts(), fn ($query) => $query->published())
            ->get()
            ->keyBy(fn (CaseStudy $case): string => (string) $case->getKey());

        primeMedia(
            $content->cta_avatar,
            $cases->pluck('homepage_photo_id')->all(),
            $cases->pluck('homepage_video_id')->all(),
            $industries->flatMap(fn (Industry $industry): array => collect($industry->proof_logos ?? [])->pluck('logo')->all())->all(),
        );

        return $this->page('Industries', [
            'heroTitle' => $content->hero_title,
            'heroDescription' => $content->hero_description,
            'navLabel' => $content->nav_label,
            'ctaAvatar' => mediaUrl($content->cta_avatar),
            'seoTitle' => $content->seo_title,
            'industries' => $industries
                ->map(fn (Industry $industry): array => $this->industry($industry, $cases))
                ->all(),
        ], $content->seo_title ?: 'Industries — Lemon Scented Tea', $content->meta_description);
    }

    /**
     * @param  Collection<string, CaseStudy>  $cases
     * @return array<string, mixed>
     */
    private function industry(Industry $industry, Collection $cases): array
    {
        return [
            'name' => $industry->name,
            'slug' => $industry->slug,
            'headline' => $industry->headline,
            'whyTitle' => $industry->why_title,
            'whyLead' => $industry->why_lead,
            'whyBody' => $industry->why_body,
            'cases' => collect($industry->cases ?? [])
                ->map(fn (mixed $id): ?CaseStudy => $cases->get((string) $id))
                ->filter()
                ->map(fn (CaseStudy $case): array => [
                    'name' => $case->client_name ?: $case->name,
                    'slug' => $case->slug,
                    'video' => mediaUrl($case->homepage_video_id),
                    'photo' => mediaUrl($case->homepage_photo_id),
                    'touchpoints' => $case->touchpoints ?? [],
                ])
                ->values()
                ->all(),
            'approachTitle' => $industry->approach_title,
            'approachItems' => collect($industry->approach_items ?? [])
                ->map(fn (array $item): array => [
                    'title' => $item['title'] ?? '',
                    'subtitle' => $item['subtitle'] ?? null,
                    'body' => $item['body'] ?? null,
                    'bullets' => array_values(array_filter($item['bullets'] ?? [])),
                ])
                ->filter(fn (array $item): bool => filled($item['title']))
                ->values()
                ->all(),
            'beliefTitle' => $industry->belief_title,
            'beliefBody' => $industry->belief_body,
            'beliefHighlight' => $industry->belief_highlight,
            'ctaText' => $industry->cta_text,
            'ctaPrimaryText' => $industry->cta_primary_text,
            'ctaPrimaryHref' => $industry->cta_primary_href,
            'ctaSecondaryText' => $industry->cta_secondary_text,
            'ctaSecondaryHref' => $industry->cta_secondary_href,
            'proofTitle' => $industry->proof_title,
            'proofLogos' => collect($industry->proof_logos ?? [])
                ->map(fn (array $item): array => [
                    'name' => $item['name'] ?? '',
                    'logo' => mediaUrl($item['logo'] ?? null),
                ])
                ->filter(fn (array $item): bool => filled($item['logo']))
                ->values()
                ->all(),
        ];
    }
}
