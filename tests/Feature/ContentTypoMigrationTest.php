<?php

use App\Models\CaseStudy;
use App\Models\Job;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('fixes the typos from the client feedback without touching slugs', function () {
    $case = CaseStudy::factory()->create([
        'story_body' => 'Come and visit Ouwehand and feel a great  responsibility.',
    ]);
    $job = Job::query()->create(['title' => 'Account Stagair(e)', 'status' => 'published']);
    $slug = $job->slug;

    $migration = require collect(glob(database_path('migrations/*_fix_content_typos_from_client_feedback.php')))->first();
    $migration->up();

    expect($case->fresh()->story_body)->toBe('Come and visit Ouwehands and feel a great responsibility.')
        ->and($job->fresh()->title)->toBe('Account Stagiair(e)')
        ->and($job->fresh()->slug)->toBe($slug)
        ->and(DB::table('about_page_contents')->where('growth_tags', 'like', '%Posistioning%')->exists())->toBeFalse();
});
