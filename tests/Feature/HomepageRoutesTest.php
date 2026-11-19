<?php

use App\Models\ContactPageContent;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the public pages', function (string $path) {
    $this->get($path)->assertOk();
})->with([
    '/',
    '/about',
    '/contact',
    '/cxbyex',
    '/jobs',
    '/blog',
    '/cases',
]);

it('redirects the duplicate homepage to the canonical one', function () {
    $this->get('/home2')->assertMovedPermanently()->assertRedirect('/');
});

it('renders the page title, description and canonical url server-side', function () {
    ContactPageContent::getSingleton()->update([
        'seo_title' => 'Contact Lemon Scented Tea',
        'meta_description' => 'Neem contact met ons op.',
    ]);

    $this->get('/contact')
        ->assertOk()
        ->assertSee('<title inertia>Contact Lemon Scented Tea</title>', escape: false)
        ->assertSee('<meta inertia="description" name="description" content="Neem contact met ons op.">', escape: false)
        ->assertSee('<link rel="canonical" href="'.url('/contact').'">', escape: false);
});

it('redirects the legacy careers url to the jobs page', function () {
    $this->get('/careers')->assertMovedPermanently()->assertRedirect('/jobs');
});

it('links the about page join-team button to the jobs page by default', function () {
    $this->get('/about')->assertInertia(fn ($page) => $page->where('joinTeamHref', '/jobs'));
});

it('preloads assets only through the html head, not a duplicate link header', function () {
    $this->get('/')->assertOk()->assertHeaderMissing('Link');
});
