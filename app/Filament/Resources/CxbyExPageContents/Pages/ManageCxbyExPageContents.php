<?php

namespace App\Filament\Resources\CxbyExPageContents\Pages;

use App\Filament\Resources\CxbyExPageContents\CxbyExPageContentResource;
use App\Models\CxbyExPageContent;
use Filament\Resources\Pages\EditRecord;

class ManageCxbyExPageContents extends EditRecord
{
    protected static string $resource = CxbyExPageContentResource::class;

    public function mount(int|string|null $record = null): void
    {
        $singleton = CxbyExPageContent::getSingleton();

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
