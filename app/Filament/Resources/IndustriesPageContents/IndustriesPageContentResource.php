<?php

namespace App\Filament\Resources\IndustriesPageContents;

use App\Filament\Resources\IndustriesPageContents\Pages\ManageIndustriesPageContents;
use App\Models\IndustriesPageContent;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IndustriesPageContentResource extends Resource
{
    protected static ?string $model = IndustriesPageContent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationLabel = 'Industries pagina';

    protected static \UnitEnum|string|null $navigationGroup = "Pagina's";

    protected static ?int $navigationSort = 6;

    protected static ?string $title = 'Industries pagina beheren';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Hero')
                ->description('De gele opening. De branches zelf beheer je onder "Industries".')
                ->schema([
                    TextInput::make('hero_title')
                        ->label('Titel')
                        ->required()
                        ->maxLength(80),

                    Textarea::make('hero_description')
                        ->label('Introtekst (vervaagt naar onder)')
                        ->rows(5),

                    TextInput::make('nav_label')
                        ->label('Label in de branche-navigatie (geel)')
                        ->maxLength(40),
                ]),

            Section::make('SEO')
                ->collapsed()
                ->schema([
                    TextInput::make('seo_title')
                        ->label('SEO titel')
                        ->maxLength(70),

                    Textarea::make('meta_description')
                        ->label('Meta description')
                        ->rows(3)
                        ->maxLength(160),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageIndustriesPageContents::route('/'),
        ];
    }
}
