<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the homepage routes', function (string $path) {
    $this->get($path)->assertOk();
})->with([
    '/',
    '/home2',
]);
