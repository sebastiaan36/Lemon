<?php

namespace App\Filament\Resources\Cases;

use App\Filament\Resources\Cases\Pages\CreateCaseStudy;
use App\Filament\Resources\Cases\Pages\EditCaseStudy;
use App\Filament\Resources\Cases\Pages\ListCaseStudies;
use App\Models\CaseStudy;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CaseStudyResource extends Resource
{
    protected static ?string $model = CaseStudy::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFilm;

    protected static ?string $navigationLabel = 'Cases';

    protected static ?string $pluralModelLabel = 'Cases';

    protected static ?string $modelLabel = 'Case';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Basis')
                ->schema([
                    TextInput::make('name')
                        ->label('Naam')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (callable $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),

                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    TextInput::make('client_name')
                        ->label('Klantnaam')
                        ->maxLength(255),

                    TextInput::make('accent_color')
                        ->label('Accentkleur')
                        ->default('#0A7949')
                        ->helperText('Bijvoorbeeld #0A7949 voor titels, lijnen en statistieken.'),

                    Toggle::make('is_featured')
                        ->label('Tonen op homepage')
                        ->helperText('Zet aan om deze case in de carousel op de homepage te tonen.')
                        ->default(false),

                    TextInput::make('sort_order')
                        ->label('Volgorde')
                        ->numeric()
                        ->default(0)
                        ->helperText('Lagere nummers worden eerst getoond.'),
                ])->columns(2),

            Section::make('Hero')
                ->schema([
                    TextInput::make('hero_subtitle')
                        ->label('Subtitel boven hero'),
                    TextInput::make('hero_title')
                        ->label('Hero titel')
                        ->required(),
                    FileUpload::make('hero_media')
                        ->label('Hero media')
                        ->disk('public')
                        ->directory('cases')
                        ->acceptedFileTypes(['image/*', 'video/*']),
                    TextInput::make('hero_duration')
                        ->label('Duur-label')
                        ->helperText('Bijvoorbeeld 0:45'),
                ])->columns(2),

            Section::make('Metadata onder hero')
                ->schema([
                    FileUpload::make('intro_logo')
                        ->label('Klein klantlogo')
                        ->image()
                        ->disk('public')
                        ->directory('cases'),
                    TagsInput::make('touchpoints')
                        ->label('Touchpoints')
                        ->placeholder('Voeg touchpoint toe'),
                    TextInput::make('overview_title')
                        ->label('Linker titelblok')
                        ->helperText('Bijvoorbeeld: With great animals comes great responsibility.'),
                    Textarea::make('overview_body')
                        ->label('Rechter introductietekst')
                        ->rows(5),
                ])->columns(2),

            Section::make('Galerijblok')
                ->schema([
                    Repeater::make('gallery_items')
                        ->label('Galerij items')
                        ->schema([
                            FileUpload::make('image')
                                ->label('Afbeelding')
                                ->image()
                                ->disk('public')
                                ->directory('cases')
                                ->required(),
                        ])
                        ->defaultItems(0)
                        ->maxItems(3)
                        ->addActionLabel('Afbeelding toevoegen')
                        ->collapsible(),
                ]),

            Section::make('Groene highlight sectie')
                ->schema([
                    TextInput::make('highlight_title')
                        ->label('Grote titel'),
                    Textarea::make('highlight_body')
                        ->label('Beschrijving')
                        ->rows(5),
                    FileUpload::make('highlight_media')
                        ->label('Visual')
                        ->disk('public')
                        ->directory('cases')
                        ->acceptedFileTypes(['image/*', 'video/*']),
                    TextInput::make('highlight_button_text')
                        ->label('Knoptekst'),
                ])->columns(2),

            Section::make('Campagneblok')
                ->schema([
                    TextInput::make('campaign_title')
                        ->label('Titel op dark image'),
                    Textarea::make('campaign_body')
                        ->label('Beschrijving op dark image')
                        ->rows(5),
                    FileUpload::make('campaign_media')
                        ->label('Grote afbeelding of video')
                        ->disk('public')
                        ->directory('cases')
                        ->acceptedFileTypes(['image/*', 'video/*']),
                ])->columns(2),

            Section::make('Storyblok')
                ->schema([
                    TextInput::make('story_title')
                        ->label('Titel'),
                    Textarea::make('story_body')
                        ->label('Beschrijving')
                        ->rows(6),
                    Repeater::make('story_images')
                        ->label('Afbeeldingen')
                        ->schema([
                            FileUpload::make('image')
                                ->label('Afbeelding')
                                ->image()
                                ->disk('public')
                                ->directory('cases')
                                ->required(),
                        ])
                        ->defaultItems(0)
                        ->maxItems(2)
                        ->addActionLabel('Afbeelding toevoegen')
                        ->collapsible(),
                ])->columns(2),

            Section::make('Resultaten')
                ->schema([
                    TextInput::make('results_heading')
                        ->label('Sectietitel')
                        ->default('Results'),
                    Repeater::make('results_stats')
                        ->label('Statistieken')
                        ->schema([
                            TextInput::make('prefix')
                                ->label('Prefix')
                                ->helperText('Bijvoorbeeld Top'),
                            TextInput::make('value')
                                ->label('Waarde')
                                ->required()
                                ->helperText('Bijvoorbeeld 2x, 15% of 10'),
                            TextInput::make('label')
                                ->label('Bijschrift')
                                ->required(),
                        ])
                        ->defaultItems(0)
                        ->maxItems(3)
                        ->addActionLabel('Stat toevoegen')
                        ->collapsible(),
                ]),

            Section::make('Optioneel onderpaneel')
                ->schema([
                    TextInput::make('optional_panel_title')
                        ->label('Titel'),
                    Textarea::make('optional_panel_body')
                        ->label('Beschrijving')
                        ->rows(4),
                    TextInput::make('optional_panel_button_text')
                        ->label('Knoptekst'),
                ])->columns(2),

            Section::make('Homepage media')
                ->schema([
                    FileUpload::make('video')
                        ->label('Homepage video')
                        ->disk('public')
                        ->directory('cases')
                        ->acceptedFileTypes(['video/mp4', 'video/webm'])
                        ->rules(['nullable', 'mimes:mp4,webm']),

                    FileUpload::make('photo')
                        ->label('Homepage foto')
                        ->image()
                        ->disk('public')
                        ->directory('cases'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),

                IconColumn::make('is_featured')
                    ->label('Op homepage')
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label('Volgorde')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Bijgewerkt')
                    ->dateTime('d-m-Y')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCaseStudies::route('/'),
            'create' => CreateCaseStudy::route('/create'),
            'edit' => EditCaseStudy::route('/{record}/edit'),
        ];
    }
}
