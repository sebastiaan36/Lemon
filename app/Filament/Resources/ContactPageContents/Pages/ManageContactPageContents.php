<?php

namespace App\Filament\Resources\ContactPageContents\Pages;

use App\Filament\Resources\ContactPageContents\ContactPageContentResource;
use App\Models\ContactPageContent;
use Filament\Resources\Pages\EditRecord;

class ManageContactPageContents extends EditRecord
{
    protected static string $resource = ContactPageContentResource::class;

    public function mount(int|string|null $record = null): void
    {
        $singleton = ContactPageContent::getSingleton();

        parent::mount($singleton->id);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
