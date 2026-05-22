<?php

namespace App\Filament\Resources\CxbyExPageContents;

use App\Filament\Resources\CxbyExPageContents\Pages\ManageCxbyExPageContents;
use App\Models\CxbyExPageContent;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CxbyExPageContentResource extends Resource
{
    protected static ?string $model = CxbyExPageContent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'CXbyEX pagina';

    protected static \UnitEnum|string|null $navigationGroup = "Pagina's";

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'CXbyEX pagina beheren';

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

                    Textarea::make('hero_subtitle')
                        ->label('Ondertitel (wit, onder CXbyEX)')
                        ->rows(3),
                ])->columns(1),

            Section::make('Brand narrative')
                ->description('De grote vervagende tekst op de gele achtergrond.')
                ->schema([
                    Textarea::make('narrative_text')
                        ->label('Tekst')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),

            Section::make('Case study kaart')
                ->description('De grote donkere afbeeldingskaart met case voorbeeld.')
                ->schema([
                    CuratorPicker::make('case_bg_image')
                        ->label('Achtergrondafbeelding kaart')
                        ->acceptedFileTypes(['image/*', 'video/*']),

                    Textarea::make('case_body_text')
                        ->label('Body tekst (links, wit)')
                        ->rows(3),

                    TextInput::make('case_client_name')
                        ->label('Klantnaam (geel)')
                        ->maxLength(100),

                    TagsInput::make('case_tags')
                        ->label('Tags (pills)')
                        ->placeholder('Voeg tag toe'),
                ])->columns(2),

            Section::make('Twee kolommen tekst')
                ->schema([
                    Textarea::make('body_col1')
                        ->label('Linker kolom')
                        ->rows(4),

                    Textarea::make('body_col2')
                        ->label('Rechter kolom')
                        ->rows(4),
                ])->columns(2),

            Section::make('Quote sectie')
                ->schema([
                    CuratorPicker::make('quote_bg_image')
                        ->label('Achtergrondafbeelding')
                        ->acceptedFileTypes(['image/*', 'video/*']),

                    Textarea::make('quote_text')
                        ->label('Citaat (geel, zonder aanhalingstekens)')
                        ->rows(3),

                    TextInput::make('quote_author')
                        ->label('Auteur')
                        ->maxLength(100),
                ])->columns(1),

            Section::make('From story to strategy — stappen')
                ->schema([
                    Repeater::make('steps')
                        ->label('Stappen')
                        ->schema([
                            TextInput::make('number')
                                ->label('Nummer')
                                ->maxLength(2)
                                ->default('01'),

                            TextInput::make('title')
                                ->label('Titel')
                                ->required()
                                ->maxLength(100),

                            Textarea::make('body')
                                ->label('Omschrijving')
                                ->rows(2),
                        ])
                        ->columns(3)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): string => $state['number'].' — '.($state['title'] ?? ''))
                        ->columnSpanFull(),
                ]),

            Section::make('Checklist CTA')
                ->schema([
                    CuratorPicker::make('checklist_image')
                        ->label('Boekcover afbeelding')
                        ->acceptedFileTypes(['image/*']),

                    TextInput::make('checklist_button_text')
                        ->label('Knoptekst')
                        ->default('Download our free checklist')
                        ->maxLength(80),

                    TextInput::make('checklist_href')
                        ->label('Link (URL of bestandspad)')
                        ->maxLength(255),
                ])->columns(3),

            Section::make('Leading brands')
                ->schema([
                    TextInput::make('brands_title_line1')
                        ->label('Eerste regel (wit)')
                        ->default('Leading brands')
                        ->maxLength(80),

                    TextInput::make('brands_title_line2')
                        ->label('Tweede regel (geel)')
                        ->default('choose Lemon')
                        ->maxLength(80),

                    Repeater::make('brand_logos')
                        ->label('Merklogo\'s')
                        ->schema([
                            CuratorPicker::make('logo')
                                ->label('Logo')
                                ->acceptedFileTypes(['image/*'])
                                ->required(),

                            TextInput::make('name')
                                ->label('Merknaam (alt tekst)')
                                ->maxLength(80),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): string => $state['name'] ?? 'Logo')
                        ->columnSpanFull(),
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
            'index' => ManageCxbyExPageContents::route('/'),
        ];
    }
}
