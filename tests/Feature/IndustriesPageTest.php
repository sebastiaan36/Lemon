<?php

use App\Models\CaseStudy;
use App\Models\Industry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the seeded aviation industry with its approach and logos', function () {
    $this->get('/industries')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Industries')
            ->where('heroTitle', 'Industries')
            ->where('navLabel', 'Our industries')
            ->where('industries.0.slug', 'aviation')
            ->has('industries.0.approachItems', 3)
            ->where('industries.0.approachItems.0.title', 'Build the brand')
            ->has('industries.0.approachItems.0.bullets', 4)
            ->has('industries.0.proofLogos', 4)
            ->where('industries.1.slug', 'financial-services'));
});

it('hides concept industries and concept cases from guests', function () {
    Industry::query()->delete();

    $published = CaseStudy::factory()->published()->create(['name' => 'Open case']);
    $concept = CaseStudy::factory()->concept()->create(['name' => 'Secret case']);

    Industry::factory()->create([
        'name' => 'Retail',
        'sort_order' => 1,
        'cases' => [(string) $concept->id, (string) $published->id],
    ]);
    Industry::factory()->concept()->create(['name' => 'Hidden industry', 'sort_order' => 2]);

    $this->get('/industries')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('industries', 1)
            ->where('industries.0.name', 'Retail')
            ->has('industries.0.cases', 1)
            ->where('industries.0.cases.0.slug', $published->slug))
        ->assertDontSee('Secret case')
        ->assertDontSee('Hidden industry');
});

it('shares published industries with the site menu', function () {
    $this->get('/contact')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('siteHeader.industries', 2)
            ->where('siteHeader.industries.0', ['name' => 'Aviation', 'slug' => 'aviation'])
            ->where('siteHeader.industries.1.slug', 'financial-services'));

    Industry::query()->where('slug', 'financial-services')->first()->update(['status' => 'concept']);

    $this->get('/contact')
        ->assertInertia(fn ($page) => $page->has('siteHeader.industries', 1));
});
