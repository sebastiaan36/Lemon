<?php

use App\Filament\Resources\IndustriesPageContents\Pages\ManageIndustriesPageContents;
use App\Models\CaseStudy;
use App\Models\IndustriesPageContent;
use App\Models\Industry;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

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

it('edits page texts and all industries from the Industries page editor', function () {
    $this->actingAs(User::factory()->create());

    $component = Livewire::test(ManageIndustriesPageContents::class)
        ->assertOk()
        ->assertFormFieldExists('hero_title')
        ->assertFormFieldExists('cta_avatar')
        ->assertFormFieldExists('industries');

    $items = $component->get('data.industries');
    expect($items)->toHaveCount(4);

    // Aviation aanpassen, Lifestyle weghalen, Financial services bovenaan zetten, Retail toevoegen.
    $keys = array_keys($items);
    $aviation = $items[$keys[0]];
    $aviation['headline'] = 'Nieuwe aviation kop';
    $aviation['approach_items'] = array_values($aviation['approach_items']);
    $aviation['approach_items'][0]['title'] = 'Nieuwe stap';

    $new = [
        $keys[1] => $items[$keys[1]],
        $keys[0] => $aviation,
        $keys[2] => $items[$keys[2]],
        'new-item' => [
            'id' => null,
            'name' => 'Retail',
            'status' => 'published',
            'headline' => 'Retail kop',
        ],
    ];

    $component
        ->fillForm(['hero_title' => 'Onze branches'])
        ->set('data.industries', $new)
        ->call('save')
        ->assertHasNoFormErrors();

    expect(IndustriesPageContent::getSingleton()->fresh()->hero_title)->toBe('Onze branches');

    $industries = Industry::query()->orderBy('sort_order')->get();
    expect($industries->pluck('slug')->all())->toBe(['financial-services', 'aviation', 'hospitality', 'retail'])
        ->and($industries[1]->headline)->toBe('Nieuwe aviation kop')
        ->and($industries[1]->approach_items[0]['title'])->toBe('Nieuwe stap')
        ->and($industries[1]->proof_logos)->toHaveCount(4)
        ->and(collect($industries[1]->proof_logos)->pluck('logo')->every(fn ($id) => is_string($id) && Media::query()->whereKey($id)->exists()))->toBeTrue();
});
