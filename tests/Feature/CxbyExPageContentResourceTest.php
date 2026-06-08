<?php

use App\Filament\Resources\CxbyExPageContents\Pages\ManageCxbyExPageContents;
use App\Models\CxbyExPageContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('renders the CXbyEX page editor in Filament', function () {
    Livewire::test(ManageCxbyExPageContents::class)
        ->assertOk()
        ->assertFormFieldExists('hero_subtitle')
        ->assertFormFieldExists('case_client_name')
        ->assertFormFieldExists('steps');
});

it('loads the singleton content into the editor', function () {
    $content = CxbyExPageContent::getSingleton();

    Livewire::test(ManageCxbyExPageContents::class)
        ->assertFormSet([
            'hero_subtitle' => $content->hero_subtitle,
            'case_client_name' => $content->case_client_name,
        ]);
});

it('saves edits to the CXbyEX page content', function () {
    CxbyExPageContent::getSingleton();

    Livewire::test(ManageCxbyExPageContents::class)
        ->fillForm([
            'hero_subtitle' => 'Nieuwe ondertitel via Filament',
            'case_client_name' => 'KLM',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(CxbyExPageContent::getSingleton()->fresh())
        ->hero_subtitle->toBe('Nieuwe ondertitel via Filament')
        ->case_client_name->toBe('KLM');
});
