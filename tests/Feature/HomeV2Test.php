<?php

use App\Filament\Resources\HomepageContents\Pages\ManageHomepageContents;
use App\Models\FloatingPill;
use App\Models\HomepageContent;
use App\Models\Industry;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders Home V2 with the same content as the homepage', function () {
    HomepageContent::getSingleton()->update(['services_intro_text' => 'Gedeelde intro']);

    $this->get('/home-v2')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('HomeV2')
            ->where('servicesIntroText', 'Gedeelde intro')
            ->has('services', 3)
            ->where('servicesVideo', null));

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->where('servicesIntroText', 'Gedeelde intro'));
});

it('passes the continuous services video to Home V2', function () {
    Bus::withoutDispatchingAfterResponses();

    Storage::fake('public');
    Storage::disk('public')->put('services-loop.mp4', 'mp4');

    $video = Media::query()->create([
        'disk' => 'public',
        'directory' => '',
        'visibility' => 'public',
        'name' => 'services-loop',
        'path' => 'services-loop.mp4',
        'ext' => 'mp4',
        'type' => 'video/mp4',
        'size' => 3,
    ]);
    HomepageContent::getSingleton()->update(['services_video' => $video->id]);

    $this->get('/home-v2')
        ->assertInertia(fn ($page) => $page
            ->where('servicesVideo.type', 'video')
            ->where('servicesVideo.url', fn (string $url) => $url !== ''));
});

it('keeps Home V2 out of search engines but not the real homepage', function () {
    $this->get('/home-v2')->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    $this->get('/')->assertDontSee('noindex', false);
});

it('lets editors pick the continuous services video', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(ManageHomepageContents::class)
        ->assertFormFieldExists('services_video');
});

it('shows the "Meet the team" pill on Home V2', function () {
    expect(FloatingPill::query()->where('page', 'home-v2')->sole())
        ->label->toBe('Meet the team')
        ->start_section->toBe('about');
});

it('titles the Financial services proof block "Proof that counts"', function () {
    expect(Industry::query()->where('slug', 'financial-services')->value('proof_title'))
        ->toBe('Proof that counts');
});
