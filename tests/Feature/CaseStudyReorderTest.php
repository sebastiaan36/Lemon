<?php

use App\Filament\Resources\Cases\Pages\ListCaseStudies;
use App\Filament\Resources\Industries\Pages\ListIndustries;
use App\Filament\Resources\Jobs\Pages\ListJobs;
use App\Models\CaseStudy;
use App\Models\Industry;
use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('reorders cases via drag and drop in the admin table', function () {
    $this->actingAs(User::factory()->create());

    CaseStudy::query()->delete();
    $a = CaseStudy::factory()->published()->create(['name' => 'A', 'sort_order' => 1]);
    $b = CaseStudy::factory()->published()->create(['name' => 'B', 'sort_order' => 2]);
    $c = CaseStudy::factory()->published()->create(['name' => 'C', 'sort_order' => 3]);

    Livewire::test(ListCaseStudies::class)
        ->assertOk()
        ->call('reorderTable', [(string) $c->id, (string) $a->id, (string) $b->id]);

    expect(CaseStudy::query()->orderBy('sort_order')->pluck('name')->all())->toBe(['C', 'A', 'B']);

    $this->get('/cases')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('cases.0.slug', $c->slug)
            ->where('cases.1.slug', $a->slug)
            ->where('cases.2.slug', $b->slug));
});

it('reorders jobs and industries via drag and drop in the admin table', function () {
    $this->actingAs(User::factory()->create());

    $ids = Industry::query()->orderBy('sort_order')->pluck('id')->map(fn ($id) => (string) $id)->reverse()->values()->all();
    Livewire::test(ListIndustries::class)
        ->call('reorderTable', $ids);
    expect(Industry::query()->orderBy('sort_order')->pluck('id')->map(fn ($id) => (string) $id)->all())->toBe($ids);

    $jobs = collect(['Designer', 'Strateeg', 'Producer'])->map(fn (string $title) => Job::query()->create(['title' => $title, 'status' => 'published']));
    $jobIds = $jobs->pluck('id')->map(fn ($id) => (string) $id)->reverse()->values()->all();
    Livewire::test(ListJobs::class)
        ->call('reorderTable', $jobIds);
    expect(Job::query()->whereKey($jobIds)->orderBy('sort_order')->pluck('id')->map(fn ($id) => (string) $id)->all())->toBe($jobIds);
});
