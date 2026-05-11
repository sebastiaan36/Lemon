<?php

namespace App\Filament\Resources\AboutPageContents\Pages;

use App\Filament\Resources\AboutPageContents\AboutPageContentResource;
use App\Models\AboutPageContent;
use Filament\Resources\Pages\EditRecord;

class ManageAboutPageContents extends EditRecord
{
    protected static string $resource = AboutPageContentResource::class;

    public function mount(int|string|null $record = null): void
    {
        $singleton = AboutPageContent::getSingleton();

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
