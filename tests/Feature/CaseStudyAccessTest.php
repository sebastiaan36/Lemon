<?php

use App\Models\CaseStudy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows guests to view a published case', function () {
    $case = CaseStudy::factory()->published()->create();

    $this->get(route('work.show', $case))->assertOk();
});

it('returns 404 for guests viewing a concept case', function () {
    $case = CaseStudy::factory()->concept()->create();

    $this->get(route('work.show', $case))->assertNotFound();
});

it('allows authenticated users to view a concept case', function () {
    $case = CaseStudy::factory()->concept()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('work.show', $case))
        ->assertOk();
});

it('hides concept cases from the homepage carousel for guests', function () {
    $published = CaseStudy::factory()->featured()->published()->create();
    $concept = CaseStudy::factory()->featured()->concept()->create();

    $response = $this->get('/');

    $response->assertOk();

    $slugs = collect($response->viewData('page')['props']['cases'])->pluck('slug');
    expect($slugs)->toContain($published->slug);
    expect($slugs)->not->toContain($concept->slug);
});
