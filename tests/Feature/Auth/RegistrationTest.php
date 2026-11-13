<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;

uses(RefreshDatabase::class);

it('has public registration disabled', function () {
    expect(Features::enabled(Features::registration()))->toBeFalse();

    $this->get('/register')->assertNotFound();
});

it('does not create an account when posting to the register endpoint', function () {
    $this->post('/register', [
        'name' => 'Indringer',
        'email' => 'indringer@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    expect(User::query()->where('email', 'indringer@example.com')->exists())->toBeFalse();
});

it('keeps guests out of the admin panel', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});
