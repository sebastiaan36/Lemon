<?php

use App\Enums\CaseStatus;
use App\Models\Industry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('publishes Hospitality and Lifestyle with the copy-doc texts', function (string $slug, string $headline, string $highlight) {
    $industry = Industry::query()->where('slug', $slug)->sole();

    expect($industry)
        ->status->toBe(CaseStatus::Published)
        ->headline->toBe($headline)
        ->belief_highlight->toBe($highlight)
        ->proof_title->toBe('Proof that delivers')
        ->cta_primary_href->toBe('/contact');
})->with([
    ['hospitality', 'Elevating the guest experience through brand, culture and creativity.', 'That’s where your brand lives.'],
    ['lifestyle', 'Designing lifestyle brands people don’t just buy, but live.', 'That’s where your brand truly lives.'],
]);

it('shows Hospitality and Lifestyle on the industries page', function () {
    $this->get('/industries')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('industries', fn ($industries) => collect($industries)->pluck('name')->intersect(['Hospitality', 'Lifestyle'])->count() === 2));
});

it('fills the Financial services belief and closing block from the copy doc', function () {
    expect(Industry::query()->where('slug', 'financial-services')->sole())
        ->belief_title->toBe('What we believe')
        ->belief_body->toStartWith('Technology may streamline finance.')
        ->belief_highlight->toBe('That’s where your brand lives.')
        ->cta_text->toStartWith('We believe the strongest financial brands grow from the inside out.')
        ->proof_title->toBe('Proof that counts');
});
