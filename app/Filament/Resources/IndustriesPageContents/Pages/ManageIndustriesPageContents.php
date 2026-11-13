<?php

namespace App\Filament\Resources\IndustriesPageContents\Pages;

use App\Filament\Resources\IndustriesPageContents\IndustriesPageContentResource;
use App\Models\IndustriesPageContent;
use Filament\Resources\Pages\EditRecord;

class ManageIndustriesPageContents extends EditRecord
{
    protected static string $resource = IndustriesPageContentResource::class;

    public function mount(int|string|null $record = null): void
    {
        $singleton = IndustriesPageContent::getSingleton();

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
