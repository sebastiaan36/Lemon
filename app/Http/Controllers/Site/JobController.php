<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobsPageContent;
use App\Support\PageViews;
use App\Support\RichContent;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Response;

class JobController extends Controller
{
    public function __invoke(): Response
    {
        $pageContent = JobsPageContent::getSingleton();
        $jobs = Job::published()->orderBy('sort_order')->orderBy('id')->get();

        primeMedia($pageContent->hero_bg_image, $jobs->pluck('apply_contact_photo')->all());

        $heroBgImage = mediaUrl($pageContent->hero_bg_image);

        return $this->page('Jobs', [
            'seoTitle' => $pageContent->seo_title,
            'metaDescription' => $pageContent->meta_description,
            'heroBgImage' => $heroBgImage,
            'heroTitle' => $pageContent->hero_title,
            'heroSubtitle' => $pageContent->hero_subtitle,
            'jobs' => $jobs->map(fn (Job $job): array => [
                'slug' => $job->slug,
                'title' => $job->title,
                'jobTitle' => $job->job_title ?: $job->title,
                'shortDescription' => $job->short_description,
                'body' => RichContent::toSafeHtml($job->body),
                'tags' => $job->tags ?? [],
                'applyButtonText' => $job->apply_button_text,
                'applyEmail' => $job->apply_email,
                'applyContactName' => $job->apply_contact_name,
                'applyContactPhoto' => mediaUrl($job->apply_contact_photo),
            ])->all(),
        ], $pageContent->seo_title, $pageContent->meta_description, $heroBgImage);
    }

    /**
     * Een job opent binnen /jobs (klik of #slug-link); de pagina meldt dat hier.
     */
    public function view(Request $request, Job $job): HttpResponse
    {
        abort_unless(Job::published()->whereKey($job->getKey())->exists(), 404);

        PageViews::record($request, $job);

        return response()->noContent();
    }
}
