<?php

namespace App\Filament\Resources\Cases\Pages;

use App\Filament\Resources\Cases\CaseStudyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCaseStudies extends ListRecords
{
    protected static string $resource = CaseStudyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
