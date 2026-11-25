<?php

namespace App\Filament\Resources\FloatingPills\Pages;

use App\Filament\Resources\FloatingPills\FloatingPillResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFloatingPill extends EditRecord
{
    protected static string $resource = FloatingPillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
