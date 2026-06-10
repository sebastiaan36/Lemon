<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('renders the beheerder accounts resource in Filament', function () {
    Livewire::test(ListUsers::class)
        ->assertOk()
        ->assertCanSeeTableRecords(User::query()->get());
});

it('renders the Filament dashboard with the beheerders navigation item', function () {
    $this->get('/admin')
        ->assertOk()
        ->assertSee('figma-assets/c6c22c65-e565-4464-8acd-8dd235f3f7f6.svg')
        ->assertSee('Beheerders')
        ->assertSee('lemon-admin-users-nav-group');
});

it('creates a beheerder account', function () {
    Livewire::test(CreateUser::class)
        ->fillForm([
            'name' => 'Nieuwe Beheerder',
            'email' => 'admin@example.com',
            'password' => 'super-secret',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::where('email', 'admin@example.com')->firstOrFail();

    expect($user->name)->toBe('Nieuwe Beheerder')
        ->and(Hash::check('super-secret', $user->password))->toBeTrue();
});

it('updates a beheerder account without resetting the password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('current-password'),
    ]);

    Livewire::test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => 'Aangepaste Beheerder',
            'email' => 'aangepast@example.com',
            'password' => null,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();

    expect($user->name)->toBe('Aangepaste Beheerder')
        ->and($user->email)->toBe('aangepast@example.com')
        ->and(Hash::check('current-password', $user->password))->toBeTrue();
});
