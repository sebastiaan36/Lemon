<?php

namespace App\Filament\Resources\JobsPageContents;

use App\Filament\Resources\JobsPageContents\Pages\ManageJobsPageContents;
use App\Models\JobsPageContent;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class JobsPageContentResource extends Resource
{
    protected static ?string $model = JobsPageContent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?string $navigationLabel = 'Jobs pagina';

    protected static \UnitEnum|string|null $navigationGroup = "Pagina's";

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Jobs pagina beheren';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('SEO')
                ->schema([
                    TextInput::make('seo_title')
                        ->label('Paginatitel (browser tab)')
                        ->maxLength(120),

                    Textarea::make('meta_description')
                        ->label('Meta beschrijving')
                        ->rows(2),
                ])->columns(1),

            Section::make('Hero')
                ->schema([
                    CuratorPicker::make('hero_bg_image')
                        ->label('Achtergrondafbeelding')
                        ->acceptedFileTypes(['image/*', 'video/*']),

                    TextInput::make('hero_title')
                        ->label('Titel (geel)')
                        ->maxLength(100),

                    TextInput::make('hero_subtitle')
                        ->label('Ondertitel (wit)')
                        ->maxLength(200),
                ])->columns(1),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageJobsPageContents::route('/'),
        ];
    }
}
