<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use App\Models\HomepageContent;
use Inertia\Response;

class HomeController extends Controller
{
    private const array VIDEO_EXTENSIONS = ['mp4', 'webm', 'mov', 'avi'];

    public function __invoke(): Response
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
            $featuredCases->pluck('homepage_photo_id')->all(),
            $featuredCases->pluck('homepage_video_id')->all(),
        );

        $heroBgImage = mediaUrl($content->hero_bg_image);

        return $this->page('Welcome', [
            'seoTitle' => $content->seo_title,
            'metaDescription' => $content->meta_description,
            'heroTitle' => $content->hero_title,
            'heroBodyText' => $content->hero_body_text,
            'heroBgImage' => $heroBgImage,
            'heroFloatingImages' => mediaUrls($content->hero_floating_images),
            'servicesIntroText' => $content->services_intro_text,
            'services' => $this->services($content->services ?? []),
            'teamAvatars' => mediaUrls($content->team_avatars),
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
        ], $content->seo_title, $content->meta_description, $heroBgImage);
    }

    /**
     * The services repeater still uses FileUpload (public disk paths) instead of Curator.
     *
     * @param  array<int, array<string, mixed>>  $services
     * @return array<int, array<string, mixed>>
     */
    private function services(array $services): array
    {
        return collect($services)->map(function (array $service): array {
            $path = $service['mediaUrl'] ?? null;

            if (is_array($path)) {
                $path = array_values($path)[0] ?? null;
            }

            $mediaUrl = publicStorageUrl(is_string($path) ? $path : null);
            $mediaType = $service['mediaType'] ?? 'image';

            if ($mediaUrl && $mediaType === 'image') {
                $extension = strtolower(pathinfo(parse_url($mediaUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));

                if (in_array($extension, self::VIDEO_EXTENSIONS, true)) {
                    $mediaType = 'video';
                }
            }

            return array_merge($service, ['mediaUrl' => $mediaUrl, 'mediaType' => $mediaType]);
        })->all();
    }
}
