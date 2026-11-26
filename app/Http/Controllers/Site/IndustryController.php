<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\CaseStudy;
use App\Models\IndustriesPageContent;
use App\Models\Industry;
use Illuminate\Support\Collection;
use Inertia\Response;

class IndustryController extends Controller
{
    private const INSIGHTS_LIMIT = 3;

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

        $blogIds = $industries
            ->flatMap(fn (Industry $industry): array => $industry->insights_blogs ?? [])
            ->map(fn (mixed $id): string => (string) $id)
            ->unique()
            ->values();

        $blogs = Blog::query()
            ->published()
            ->whereKey($blogIds->all())
            ->get()
            ->keyBy(fn (Blog $blog): string => (string) $blog->getKey());

        $latestBlogs = Blog::query()
            ->published()
            ->latest('published_at')
            ->latest('id')
            ->limit(self::INSIGHTS_LIMIT)
            ->get();

        primeMedia(
            $content->hero_bg_image,
            $content->cta_avatar,
            $cases->pluck('homepage_photo_id')->all(),
            $cases->pluck('homepage_video_id')->all(),
            $industries->flatMap(fn (Industry $industry): array => collect($industry->proof_logos ?? [])->pluck('logo')->all())->all(),
        );

        $heroBgImage = mediaItem($content->hero_bg_image);

        return $this->page('Industries', [
            'heroTitle' => $content->hero_title,
            'heroDescription' => $content->hero_description,
            'heroBgImage' => $heroBgImage,
            'navLabel' => $content->nav_label,
            'ctaAvatar' => mediaUrl($content->cta_avatar),
            'seoTitle' => $content->seo_title,
            'industries' => $industries
                ->map(fn (Industry $industry): array => $this->industry($industry, $cases, $blogs, $latestBlogs))
                ->all(),
        ], $content->seo_title ?: 'Industries — Lemon Scented Tea', $content->meta_description, $heroBgImage['url'] ?? null);
    }

    /**
     * @param  Collection<string, CaseStudy>  $cases
     * @param  Collection<string, Blog>  $blogs
     * @param  Collection<int, Blog>  $latestBlogs
     * @return array<string, mixed>
     */
    private function industry(Industry $industry, Collection $cases, Collection $blogs, Collection $latestBlogs): array
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
            'impactTitle' => $industry->impact_title,
            'impactBody' => $industry->impact_body,
            'impactStats' => collect($industry->impact_stats ?? [])
                ->map(fn (array $stat): array => [
                    'value' => $stat['value'] ?? '',
                    'label' => $stat['label'] ?? '',
                ])
                ->filter(fn (array $stat): bool => filled($stat['value']))
                ->values()
                ->all(),
            'insightsTitle' => $industry->insights_title,
            'insights' => $this->insights($industry, $blogs, $latestBlogs),
        ];
    }

    /**
     * Gekozen blogs in selectievolgorde; zonder keuze de nieuwste.
     *
     * @param  Collection<string, Blog>  $blogs
     * @param  Collection<int, Blog>  $latestBlogs
     * @return list<array{title: string, slug: string, category: string|null}>
     */
    private function insights(Industry $industry, Collection $blogs, Collection $latestBlogs): array
    {
        $chosen = collect($industry->insights_blogs ?? [])
            ->map(fn (mixed $id): ?Blog => $blogs->get((string) $id))
            ->filter();

        return ($chosen->isNotEmpty() ? $chosen : $latestBlogs)
            ->take(self::INSIGHTS_LIMIT)
            ->map(fn (Blog $blog): array => [
                'title' => $blog->title,
                'slug' => $blog->slug,
                'category' => $blog->category,
            ])
            ->values()
            ->all();
    }
}
