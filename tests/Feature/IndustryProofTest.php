<?php

use App\Enums\CaseStatus;
use App\Models\Blog;
use App\Models\Industry;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeBlog(string $title, string $publishedAt, string $status = 'published'): Blog
{
    return Blog::create([
        'title' => $title,
        'category' => 'Branding',
        'status' => $status,
        'published_at' => $publishedAt,
    ]);
}

it('seeds the HCOB impact block for Financial services', function () {
    $industry = Industry::query()->where('slug', 'financial-services')->sole();

    expect($industry->impact_title)->toBe('HCOB | Trust Through Every Touchpoint')
        ->and($industry->impact_stats)->toHaveCount(4)
        ->and($industry->impact_stats[0])->toBe(['value' => '+23%', 'label' => 'Brand awareness'])
        ->and($industry->insights_title)->toBe('Insights');
});

it('gives Lifestyle the client logos that are already in the media library', function () {
    expect(collect(Industry::query()->where('slug', 'lifestyle')->value('proof_logos'))->pluck('name')->all())
        ->toBe(['Cortina', 'Loods 5']);
});

it('passes impact stats and the latest published blogs as insights', function () {
    makeBlog('Oud', '2026-01-01');
    makeBlog('Nieuw', '2026-11-01');
    makeBlog('Middel', '2026-06-01');
    makeBlog('Nog ouder', '2025-01-01');
    makeBlog('Geheim', '2026-11-20', CaseStatus::Concept->value);

    $this->get('/industries')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('industries.1.slug', 'financial-services')
            ->has('industries.1.impactStats', 4)
            ->where('industries.1.impactStats.1', ['value' => '+400%', 'label' => 'Web traffic'])
            ->where('industries.1.insights', fn ($insights) => collect($insights)->pluck('title')->all() === ['Nieuw', 'Middel', 'Oud']));
});

it('uses the chosen blogs in selection order when an industry picks them', function () {
    $first = makeBlog('Eerste keuze', '2025-01-01');
    $second = makeBlog('Tweede keuze', '2025-02-01');
    $concept = makeBlog('Concept', '2025-03-01', CaseStatus::Concept->value);
    makeBlog('Nieuwste', '2026-11-01');

    Industry::query()->where('slug', 'aviation')->update([
        'insights_blogs' => json_encode([(string) $second->id, (string) $concept->id, (string) $first->id]),
    ]);

    $this->get('/industries')
        ->assertInertia(fn ($page) => $page
            ->where('industries.0.slug', 'aviation')
            ->where('industries.0.insights', fn ($insights) => collect($insights)->pluck('title')->all() === ['Tweede keuze', 'Eerste keuze'])
            ->has('industries.0.impactStats', 0));
});
