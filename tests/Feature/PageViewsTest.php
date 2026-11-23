<?php

use App\Filament\Widgets\PopularContent;
use App\Filament\Widgets\QuickActions;
use App\Models\Blog;
use App\Models\CaseStudy;
use App\Models\Job;
use App\Models\User;
use App\Support\PageViews;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(RefreshDatabase::class);

const BROWSER = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/605.1.15 Safari/605.1.15';

function views(string $type): int
{
    return (int) DB::table('page_views')->where('viewable_type', $type)->sum('views');
}

it('counts a case view once per visitor per day', function () {
    $case = CaseStudy::factory()->published()->create();

    $this->withHeader('User-Agent', BROWSER)->get("/cases/{$case->slug}")->assertOk();
    $this->withHeader('User-Agent', BROWSER)->get("/cases/{$case->slug}")->assertOk();
    expect(views('case'))->toBe(1);

    // Nieuwe bezoeker (nieuwe sessie) telt wel.
    $this->flushSession();
    $this->withHeader('User-Agent', BROWSER)->get("/cases/{$case->slug}")->assertOk();
    expect(views('case'))->toBe(2);
});

it('does not count bots, logged-in users or concept cases', function () {
    $case = CaseStudy::factory()->published()->create();
    $concept = CaseStudy::factory()->concept()->create();

    $this->withHeader('User-Agent', 'Googlebot/2.1')->get("/cases/{$case->slug}")->assertOk();
    $this->get("/cases/{$case->slug}")->assertOk(); // geen user-agent

    $this->actingAs(User::factory()->create());
    $this->withHeader('User-Agent', BROWSER)->get("/cases/{$case->slug}")->assertOk();
    $this->withHeader('User-Agent', BROWSER)->get("/cases/{$concept->slug}")->assertOk();

    expect(views('case'))->toBe(0);
});

it('counts blog views', function () {
    $blog = Blog::query()->create(['title' => 'Mijn blog', 'status' => 'published', 'published_at' => now()->subDay()]);

    $this->withHeader('User-Agent', BROWSER)->get("/blog/{$blog->slug}")->assertOk();

    expect(views('blog'))->toBe(1);
});

it('counts job views via the jobs page endpoint', function () {
    $job = Job::query()->create(['title' => 'Designer', 'status' => 'published']);
    $concept = Job::query()->create(['title' => 'Geheim', 'status' => 'concept']);

    $this->withHeader('User-Agent', BROWSER)->post("/jobs/{$job->slug}/view")->assertNoContent();
    $this->withHeader('User-Agent', BROWSER)->post("/jobs/{$concept->slug}/view")->assertNotFound();

    expect(views('job'))->toBe(1);
});

it('ranks the top 5 for this month and all time', function () {
    $cases = CaseStudy::factory()->published()->count(7)->create();
    $old = $cases[0];

    DB::table('page_views')->insert([
        ['viewable_type' => 'case', 'viewable_id' => $old->id, 'viewed_on' => now()->subMonths(2)->toDateString(), 'views' => 100],
        ...$cases->skip(1)->values()->map(fn ($case, $i) => [
            'viewable_type' => 'case', 'viewable_id' => $case->id, 'viewed_on' => now()->toDateString(), 'views' => $i + 1,
        ])->all(),
    ]);

    $allTime = PageViews::top(CaseStudy::class);
    $month = PageViews::top(CaseStudy::class, now()->startOfMonth());

    expect($allTime)->toHaveCount(5)
        ->and($allTime->first()->is($old))->toBeTrue()
        ->and((int) $allTime->first()->views_count)->toBe(100)
        ->and($month)->toHaveCount(5)
        ->and($month->contains($old))->toBeFalse()
        ->and((int) $month->first()->views_count)->toBe(6);
});

it('shows the quick actions and popular content on the dashboard', function () {
    $this->actingAs(User::factory()->create());
    $case = CaseStudy::factory()->published()->create(['name' => 'Toppertje']);
    DB::table('page_views')->insert(['viewable_type' => 'case', 'viewable_id' => $case->id, 'viewed_on' => now()->toDateString(), 'views' => 42]);

    $this->get('/admin')->assertOk();

    Livewire::test(QuickActions::class)
        ->assertSee('Nieuwe case')->assertSee('Nieuwe blog')->assertSee('Nieuwe job')
        ->assertSee('/admin/cases/case-studies/create', false);

    Livewire::test(PopularContent::class)
        ->assertSee('Toppertje')
        ->assertSee('42')
        ->assertSee('Nog geen weergaven.');
});
