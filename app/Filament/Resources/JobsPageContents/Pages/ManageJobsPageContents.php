<?php

namespace App\Filament\Resources\JobsPageContents\Pages;

use App\Filament\Resources\JobsPageContents\JobsPageContentResource;
use App\Models\JobsPageContent;
use Filament\Resources\Pages\EditRecord;

class ManageJobsPageContents extends EditRecord
{
    protected static string $resource = JobsPageContentResource::class;

    public function mount(int|string|null $record = null): void
    {
        $singleton = JobsPageContent::getSingleton();

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
