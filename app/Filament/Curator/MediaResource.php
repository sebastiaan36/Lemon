<?php

namespace App\Filament\Curator;

use App\Filament\Curator\Pages\CreateMedia;
use App\Filament\Curator\Pages\EditMedia;
use App\Filament\Curator\Pages\ListMedia;
use Awcodes\Curator\Resources\Media\MediaResource as BaseMediaResource;
use Filament\Tables\Table;

class MediaResource extends BaseMediaResource
{
    /**
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        return MediaTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMedia::route('/'),
            'create' => CreateMedia::route('/create'),
            'edit' => EditMedia::route('/{record}/edit'),
        ];
    }
}
