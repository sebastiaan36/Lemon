<?php

use App\Filament\Resources\AboutPageContents\Pages\ManageAboutPageContents;
use App\Models\AboutPageContent;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

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

it('passes the Credible Creativity Formula texts to the about page', function () {
    $this->get('/about')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('dimensionsFormulaTitle', 'Lemon’s Credible Creativity Formula')
            ->where('dimensionsFormulaSubtitle', 'How brand credibility drives growth')
            ->where('dimensionsCenterTitle', 'Brand narrative')
            ->where('dimensionsCenterSubtitle', 'Promise & Values')
            ->where('dimensionsItems', [
                ['name' => 'Brand', 'subtitle' => 'Advertising, PR & social'],
                ['name' => 'Employee experience', 'subtitle' => 'Internal teams'],
                ['name' => 'Proposition', 'subtitle' => 'Core product & NPD'],
                ['name' => 'Customer experience', 'subtitle' => 'Key touchpoints & customer care'],
            ]));
});

it('opens with the "We are Lemon" video hero instead of the intro quote', function () {
    $this->get('/about')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('heroTitle', 'We are Lemon')
            ->where('heroDescription', fn (string $text) => str_starts_with($text, 'We compare what we do to the ritual of making tea.'))
            ->where('heroBgImage', null)
            ->missing('introQuoteLine1')
            ->missing('introQuoteLine2')
            ->missing('introBgImage')
            ->missing('approachButtonText')
            ->missing('approachButtonHref'));
});

it('passes the hero background video from the CMS', function () {
    Storage::disk('public')->put('tea.mp4', 'video-bytes');
    $video = Media::query()->create([
        'disk' => 'public', 'directory' => '', 'visibility' => 'public',
        'name' => 'tea', 'path' => 'tea.mp4', 'ext' => 'mp4', 'type' => 'video/mp4', 'size' => 11,
    ]);

    AboutPageContent::getSingleton()->update(['hero_bg_image' => $video->id]);

    $this->get('/about')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('heroBgImage.type', 'video')
            ->where('heroBgImage.url', fn (string $url) => str_ends_with($url, '.mp4')));
});

it('edits the hero and drops the intro quote and approach button fields in the About editor', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(ManageAboutPageContents::class)
        ->assertOk()
        ->assertFormFieldExists('hero_bg_image')
        ->assertFormFieldExists('hero_title')
        ->assertFormFieldExists('hero_description')
        ->assertFormFieldDoesNotExist('intro_quote_line1')
        ->assertFormFieldDoesNotExist('intro_quote_line2')
        ->assertFormFieldDoesNotExist('intro_bg_image')
        ->assertFormFieldDoesNotExist('approach_button_text')
        ->assertFormFieldDoesNotExist('approach_button_href')
        ->fillForm(['hero_title' => 'We are Lemon!'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(AboutPageContent::getSingleton()->fresh()->hero_title)->toBe('We are Lemon!');
});
