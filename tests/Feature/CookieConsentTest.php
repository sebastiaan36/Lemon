<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('sets Google consent to denied by default and skips GTM without an id', function () {
    config(['services.google_tag_manager.id' => null]);

    $this->get('/')
        ->assertOk()
        ->assertSee("gtag('consent', 'default'", false)
        ->assertSee('lemon_consent', false)
        ->assertDontSee('googletagmanager.com', false);
});

it('loads Google Tag Manager when an id is configured', function () {
    config(['services.google_tag_manager.id' => 'GTM-TEST123']);

    $this->get('/')
        ->assertOk()
        ->assertSee('googletagmanager.com/gtm.js', false)
        ->assertSee('GTM-TEST123', false);
});
