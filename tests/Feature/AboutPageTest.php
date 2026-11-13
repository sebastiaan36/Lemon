<?php

use App\Models\AboutPageContent;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the about page with the new design sections', function () {
    $this->get('/about')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('About')
            ->where('dimensionsTitle', 'The 4 dimensions of Total Brand Growth')
            ->where('dimensionsCenterTitle', 'Brand narrative')
            ->has('dimensionsItems', 4)
            ->where('dimensionsItems.0.name', 'Brand')
            ->where('approachTitle', 'Our Brand Narrative Approach™')
            ->where('narrativeTitle', 'Your Credible Brand narrative')
            ->where('narrativeImage', null)
            ->where('narrativeTags', [])
            ->where('growthBody', fn (string $body) => str_ends_with($body, 'We call this **Total Brand Growth.**'))
            ->missing('credibleCases'));
});

it('passes edited narrative content from the CMS', function () {
    AboutPageContent::getSingleton()->update([
        'narrative_title' => 'Custom narrative',
        'narrative_client_name' => 'Veloretti',
        'narrative_tags' => ['Positioning', 'Activations'],
    ]);

    $this->get('/about')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('narrativeTitle', 'Custom narrative')
            ->where('narrativeClientName', 'Veloretti')
            ->where('narrativeTags', ['Positioning', 'Activations']));
});
