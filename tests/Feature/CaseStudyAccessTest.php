<?php

use App\Filament\Resources\Cases\Pages\ListCaseStudies;
use App\Models\CaseStudy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('allows guests to view a published case', function () {
    $case = CaseStudy::factory()->published()->create();

    $this->get(route('cases.show', $case))->assertOk();
});

it('uses Cases as the Filament case resource label', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(ListCaseStudies::class)
        ->assertOk()
        ->assertSee('Cases')
        ->assertDontSee('Work');
});

it('returns 404 for guests viewing a concept case', function () {
    $case = CaseStudy::factory()->concept()->create();

    $this->get(route('cases.show', $case))->assertNotFound();
});

it('allows authenticated users to view a concept case', function () {
    $case = CaseStudy::factory()->concept()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('cases.show', $case))
        ->assertOk();
});

it('passes single case hero title lines as separate fields', function () {
    $case = CaseStudy::factory()->published()->create([
        'hero_title' => 'Legacy fallback',
        'hero_title_line_1' => 'First line',
        'hero_title_line_2' => 'Second line',
        'hero_title_line_3' => 'Third line',
    ]);

    $response = $this->get(route('cases.show', $case));

    $response->assertOk();

    expect($response->viewData('page')['props']['caseStudy']['heroTitleLines'])
        ->toBe(['First line', 'Second line', 'Third line']);
});

it('passes the editable pre-callout carousel block to the single case page', function () {
    $case = CaseStudy::factory()->published()->create([
        'pre_callout_title' => "See yourself in\na chimpanzee",
        'pre_callout_body' => 'The new brand narrative encourages people to visit.',
        'pre_callout_gallery_items' => [],
    ]);

    $response = $this->get(route('cases.show', $case));

    $response->assertOk();

    expect($response->viewData('page')['props']['caseStudy'])
        ->preCalloutTitle->toBe("See yourself in\na chimpanzee")
        ->preCalloutBody->toBe('The new brand narrative encourages people to visit.')
        ->preCalloutGalleryItems->toBe([]);
});

it('passes the chosen style variant to the single case page', function () {
    $case = CaseStudy::factory()->published()->create([
        'style_variant' => 2,
    ]);

    $response = $this->get(route('cases.show', $case));

    $response->assertOk();

    expect($response->viewData('page')['props']['caseStudy']['styleVariant'])->toBe(2);
});

it('defaults the style variant to the first kleurstelling', function () {
    $case = CaseStudy::factory()->published()->create();

    $response = $this->get(route('cases.show', $case));

    $response->assertOk();

    expect($response->viewData('page')['props']['caseStudy']['styleVariant'])->toBe(1);
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
