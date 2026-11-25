<?php

namespace App\Filament\Resources\FloatingPills\Pages;

use App\Filament\Resources\FloatingPills\FloatingPillResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFloatingPills extends ListRecords
{
    protected static string $resource = FloatingPillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
