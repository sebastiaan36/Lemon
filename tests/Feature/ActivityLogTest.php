<?php

use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Models\ActivityLog;
use App\Models\Blog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['name' => 'Test Beheerder']);
    $this->actingAs($this->admin);
});

it('logs the admin, action and timestamp when a record is created', function () {
    $blog = Blog::create([
        'title' => 'Nieuw blogartikel',
        'body' => 'Inhoud van het artikel',
    ]);

    $log = ActivityLog::query()->where('subject_type', Blog::class)->latest('id')->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($this->admin->id)
        ->and($log->user_name)->toBe('Test Beheerder')
        ->and($log->action)->toBe('created')
        ->and($log->subject_id)->toBe($blog->id)
        ->and($log->subject_label)->toBe('Nieuw blogartikel')
        ->and($log->created_at)->not->toBeNull();
});

it('logs old and new values when a record is updated', function () {
    $blog = Blog::create(['title' => 'Oude titel', 'body' => 'Tekst']);

    $blog->update(['title' => 'Nieuwe titel']);

    $log = ActivityLog::query()
        ->where('subject_type', Blog::class)
        ->where('action', 'updated')
        ->latest('id')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->changes)->toHaveKey('title')
        ->and($log->changes['title']['old'])->toBe('Oude titel')
        ->and($log->changes['title']['new'])->toBe('Nieuwe titel');
});

it('does not log an update when nothing changed', function () {
    $blog = Blog::create(['title' => 'Titel', 'body' => 'Tekst']);

    ActivityLog::query()->delete();

    $blog->update(['title' => 'Titel']);

    expect(ActivityLog::query()->count())->toBe(0);
});

it('logs a deletion', function () {
    $blog = Blog::create(['title' => 'Weg ermee', 'body' => 'Tekst']);

    $blog->delete();

    $log = ActivityLog::query()
        ->where('subject_type', Blog::class)
        ->where('action', 'deleted')
        ->latest('id')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->subject_label)->toBe('Weg ermee');
});

it('masks passwords in the log when a beheerder is changed', function () {
    $this->admin->update(['password' => 'nieuw-geheim-wachtwoord']);

    $log = ActivityLog::query()
        ->where('subject_type', User::class)
        ->where('action', 'updated')
        ->latest('id')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->changes['password']['new'])->toBe('••••••••')
        ->and($log->changes['password']['old'])->toBe('••••••••');
});

it('renders the logboek resource in Filament', function () {
    Blog::create(['title' => 'Zichtbaar in log', 'body' => 'Tekst']);

    Livewire::test(ListActivityLogs::class)
        ->assertOk()
        ->assertCanSeeTableRecords(ActivityLog::query()->get());
});
