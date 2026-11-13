<?php

namespace App\Filament\Resources\HomepageContents\Pages;

use App\Filament\Resources\HomepageContents\HomepageContentResource;
use App\Models\HomepageContent;
use Filament\Resources\Pages\EditRecord;

class ManageHomepageContents extends EditRecord
{
    protected static string $resource = HomepageContentResource::class;

    /**
     * Laad altijd de enkele homepage-rij (singleton-patroon).
     */
    public function mount(int|string|null $record = null): void
    {
        $singleton = HomepageContent::getSingleton();

        parent::mount($singleton->id);
    }

    protected function getHeaderActions(): array
    {
        return []; // Geen delete-knop voor de singleton
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
