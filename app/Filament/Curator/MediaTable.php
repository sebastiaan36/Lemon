<?php

namespace App\Filament\Curator;

use Awcodes\Curator\Resources\Media\Tables\MediaTable as BaseMediaTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MediaTable extends BaseMediaTable
{
    /**
     * @throws \Exception
     */
    public static function configure(Table $table): Table
    {
        return parent::configure($table)
            ->searchable(['name', 'title', 'alt', 'caption', 'description'])
            ->defaultPaginationPageOption(24)
            ->paginationPageOptions([12, 24, 48, 96, 'all'])
            ->filters([
                SelectFilter::make('type')
                    ->label('Soort')
                    ->options([
                        'image' => 'Afbeeldingen',
                        'video' => 'Video\'s',
                        'document' => 'Documenten',
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['value'] ?? null) {
                            'image' => $query->where('type', 'LIKE', 'image%'),
                            'video' => $query->where('type', 'LIKE', 'video%'),
                            'document' => $query->where('type', 'NOT LIKE', 'image%')->where('type', 'NOT LIKE', 'video%'),
                            default => $query,
                        };
                    }),
            ]);
    }
}
