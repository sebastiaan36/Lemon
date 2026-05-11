<?php

namespace App\Filament\Resources\Cases\Pages;

use App\Filament\Resources\Cases\CaseStudyResource;
use App\Models\CaseStudy;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditCaseStudy extends EditRecord
{
    protected static string $resource = CaseStudyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')
                ->label('Case bekijken')
                ->icon(Heroicon::OutlinedEye)
                ->url(fn (): string => route('cases.show', ['caseStudy' => $this->getViewableSlug()]))
                ->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }

    protected function getViewableSlug(): string
    {
        /** @var CaseStudy $record */
        $record = $this->getRecord();

        return $record->slug;
    }
}
