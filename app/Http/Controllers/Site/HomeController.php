<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use App\Models\HomepageContent;
use App\Support\MediaResolver;
use Illuminate\Support\Arr;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        return $this->render('Welcome');
    }

    /**
     * Alternatieve homepage voor de interne review: cases vóór services en één
     * doorlopende services-video. Tijdelijk, dus niet geïndexeerd.
     */
    public function v2(): Response
    {
        return $this->render('HomeV2', noindex: true);
    }

    private function render(string $component, bool $noindex = false): Response
    {
        $content = HomepageContent::getSingleton();
        $featuredCases = CaseStudy::featured()->get();

        primeMedia(
            $content->hero_bg_image,
            $content->hero_floating_images,
            $content->team_avatars,
            $content->testimonial_author_avatar,
            $content->testimonial_logo,
            $content->client_logos,
            $content->team_photos,
            $content->services_video,
            $featuredCases->pluck('homepage_photo_id')->all(),
            $featuredCases->pluck('homepage_video_id')->all(),
            collect($content->services ?? [])->pluck('media')->all(),
        );

        $heroBgImage = mediaUrl($content->hero_bg_image);
        $heroIsVideo = (mediaItem($content->hero_bg_image)['type'] ?? null) === 'video';
        $servicesVideo = mediaItem($content->services_video);

        return $this->page($component, [
            'seoTitle' => $content->seo_title,
            'metaDescription' => $content->meta_description,
            'heroTitle' => $content->hero_title,
            'heroBodyText' => $content->hero_body_text,
            'heroBgImage' => $heroBgImage,
            'heroFloatingImages' => mediaUrls($content->hero_floating_images),
            'servicesIntroText' => $content->services_intro_text,
            'services' => $this->services($content->services ?? []),
            'servicesVideo' => $servicesVideo ? [
                'url' => $servicesVideo['url'],
                'type' => $servicesVideo['type'] === 'video' ? 'video' : 'image',
            ] : null,
            'cases' => $featuredCases->map(fn (CaseStudy $case): array => [
                'name' => $case->name,
                'slug' => $case->slug,
                'video' => mediaUrl($case->homepage_video_id),
                'photo' => mediaUrl($case->homepage_photo_id),
            ])->all(),
            'testimonialQuote' => $content->testimonial_quote,
            'testimonialAuthor' => $content->testimonial_author,
            'testimonialAuthorAvatar' => mediaUrl($content->testimonial_author_avatar),
            'testimonialLogo' => mediaUrl($content->testimonial_logo),
            'clientLogos' => mediaUrls($content->client_logos),
            'teamDescription' => $content->team_description,
            'teamButtonText' => $content->team_button_text,
            'teamButtonHref' => $content->team_button_href,
            'teamPhotos' => mediaUrls($content->team_photos),
        ], $content->seo_title ?: 'Lemon Scented Tea — Credible Creativity', $content->meta_description, $heroBgImage, preloadSizes: $heroIsVideo ? '(max-width: 767px) 50vw, 100vw' : '100vw', noindex: $noindex);
    }

    /**
     * Resolve each service's media library item to a URL (Bunny Stream for
     * videos, WebP for images) and derive whether it is a video.
     *
     * @param  array<int, array<string, mixed>>  $services
     * @return array<int, array{name?: string, description?: string, tags?: array<int, string>, mediaUrl: ?string, mediaType: string}>
     */
    private function services(array $services): array
    {
        $resolver = app(MediaResolver::class);

        return collect($services)->map(function (array $service) use ($resolver): array {
            $media = $resolver->find($service['media'] ?? null);

            return [
                ...Arr::except($service, ['media', 'mediaUrl', 'mediaType']),
                'mediaUrl' => $media ? $resolver->url($media->getKey()) : null,
                'mediaType' => $media && str_starts_with((string) $media->type, 'video/') ? 'video' : 'image',
            ];
        })->all();
    }
}
