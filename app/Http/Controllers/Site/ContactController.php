<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\ContactPageContent;
use App\Models\Job;
use Inertia\Response;

class ContactController extends Controller
{
    public function __invoke(): Response
    {
        $content = ContactPageContent::getSingleton();
        $members = collect($content->team_members ?? []);

        primeMedia(
            $content->hero_bg_image,
            $content->client_logos,
            $members->pluck('photo')->all(),
        );

        $heroBgImage = mediaUrl($content->hero_bg_image);

        return $this->page('Contact', [
            'seoTitle' => $content->seo_title,
            'metaDescription' => $content->meta_description,
            'heroTitle' => $content->hero_title,
            'heroBgImage' => $heroBgImage,
            'heroAddress' => $content->hero_address,
            'heroPhone' => $content->hero_phone,
            'heroEmail' => $content->hero_email,
            'introText' => $content->intro_text,
            'teamMembers' => $members->map(fn (array $member): array => [
                'name' => $member['name'] ?? '',
                'role' => $member['role'] ?? '',
                'phone' => $member['phone'] ?? null,
                'email' => $member['email'] ?? null,
                'photo' => mediaUrl($member['photo'] ?? null),
            ])->all(),
            'clientLogosLabel' => $content->client_logos_label,
            'clientLogos' => mediaUrls($content->client_logos),
            'joinIntroText' => $content->join_intro_text,
            'joinTitleLine1' => $content->join_title_line1,
            'joinTitleLine2' => $content->join_title_line2,
            'joinJobs' => Job::published()->orderBy('sort_order')->orderBy('id')->limit(5)->get()
                ->map(fn (Job $job): array => ['title' => $job->title, 'slug' => $job->slug])
                ->all(),
            'joinButtonText' => $content->join_button_text,
        ], $content->seo_title, $content->meta_description, $heroBgImage);
    }
}
