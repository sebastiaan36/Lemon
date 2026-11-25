<?php

use App\Enums\PillPage;
use App\Filament\Resources\FloatingPills\Pages\CreateFloatingPill;
use App\Filament\Resources\FloatingPills\Pages\EditFloatingPill;
use App\Filament\Resources\FloatingPills\Pages\ListFloatingPills;
use App\Models\FloatingPill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('seeds only the "Meet the team" and "Tell us about your project" pills', function () {
    expect(FloatingPill::query()->pluck('label')->unique()->sort()->values()->all())
        ->toBe(['Meet the team', 'Tell us about your project']);

    $meetTheTeam = FloatingPill::query()->where('label', 'Meet the team')->where('page', PillPage::Home)->sole();

    expect($meetTheTeam)
        ->page->toBe(PillPage::Home)
        ->start_section->toBe('about')
        ->end_section->toBe('about')
        ->use_team_avatars->toBeTrue();
});

it('shares the active pills of a page with the site', function () {
    FloatingPill::factory()->inactive()->create(['label' => 'Hidden pill']);

    $this->get('/about')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('floatingPills', fn ($pills) => collect($pills)->where('page', 'about')->pluck('label')->all() === ['Tell us about your project'])
            ->where('floatingPills', fn ($pills) => collect($pills)->firstWhere('page', 'about') === [
                'page' => 'about',
                'label' => 'Tell us about your project',
                'href' => '/contact',
                'avatars' => [FloatingPill::FALLBACK_AVATAR],
                'startSection' => 'credible',
                'endSection' => 'team-photos',
                'desktopBottom' => 24,
            ]));
});

it('falls back to the default team avatars for the "Meet the team" pill', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('floatingPills', fn ($pills) => collect($pills)->firstWhere('page', 'home')['avatars'] === FloatingPill::FALLBACK_TEAM_AVATARS));
});

it('refreshes the shared pills after saving', function () {
    $this->get('/jobs')->assertOk();

    FloatingPill::factory()->create(['page' => PillPage::Jobs, 'start_section' => 'hero', 'end_section' => 'jobs', 'label' => 'New jobs pill']);

    $this->get('/jobs')
        ->assertInertia(fn ($page) => $page
            ->where('floatingPills', fn ($pills) => collect($pills)->contains('label', 'New jobs pill')));
});

describe('admin', function () {
    beforeEach(function () {
        $this->actingAs(User::factory()->create());
    });

    it('lists the pills', function () {
        Livewire::test(ListFloatingPills::class)
            ->assertOk()
            ->assertCanSeeTableRecords(FloatingPill::all());
    });

    it('creates a pill for a page section range', function () {
        Livewire::test(CreateFloatingPill::class)
            ->fillForm([
                'label' => 'Tell us about your project',
                'href' => '/contact',
                'page' => PillPage::Contact->value,
                'start_section' => 'intro',
                'end_section' => 'clients',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        expect(FloatingPill::query()->where('page', PillPage::Contact)->sole())
            ->start_section->toBe('intro')
            ->end_section->toBe('clients')
            ->is_active->toBeTrue();
    });

    it('rejects an end section before the start section', function () {
        Livewire::test(CreateFloatingPill::class)
            ->fillForm([
                'label' => 'Backwards',
                'href' => '/contact',
                'page' => PillPage::Contact->value,
                'start_section' => 'clients',
                'end_section' => 'intro',
            ])
            ->call('create')
            ->assertHasFormErrors(['end_section']);
    });

    it('rejects a section that is not on the chosen page', function () {
        Livewire::test(CreateFloatingPill::class)
            ->fillForm([
                'label' => 'Wrong section',
                'href' => '/contact',
                'page' => PillPage::Contact->value,
                'start_section' => 'team-photos',
                'end_section' => 'join',
            ])
            ->call('create')
            ->assertHasFormErrors(['start_section']);
    });

    it('edits a pill', function () {
        $pill = FloatingPill::factory()->create();

        Livewire::test(EditFloatingPill::class, ['record' => $pill->getRouteKey()])
            ->fillForm(['label' => 'Meet us', 'end_section' => 'international'])
            ->call('save')
            ->assertHasNoFormErrors();

        expect($pill->fresh())
            ->label->toBe('Meet us')
            ->end_section->toBe('international');
    });
});

it('points the About pills at sections that still exist after removing the intro quote', function () {
    $sections = array_keys(PillPage::About->sections());

    expect($sections)->not->toContain('intro-quote')
        ->and($sections[1])->toBe('credible');

    FloatingPill::query()->where('page', PillPage::About)->get()
        ->each(fn (FloatingPill $pill) => expect($sections)
            ->toContain($pill->start_section)
            ->toContain($pill->end_section));
});
