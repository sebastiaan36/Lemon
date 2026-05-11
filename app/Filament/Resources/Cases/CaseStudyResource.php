<?php

namespace App\Filament\Resources\Cases;

use App\Enums\CaseStatus;
use App\Filament\Resources\Cases\Pages\CreateCaseStudy;
use App\Filament\Resources\Cases\Pages\EditCaseStudy;
use App\Filament\Resources\Cases\Pages\ListCaseStudies;
use App\Models\CaseStudy;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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

                    Select::make('status')
                        ->label('Status')
                        ->options(collect(CaseStatus::cases())->mapWithKeys(fn (CaseStatus $status): array => [$status->value => $status->label()])->all())
                        ->default(CaseStatus::Concept->value)
                        ->required()
                        ->helperText('Concept-cases zijn alleen zichtbaar voor ingelogde gebruikers.'),
                ])->columns(2),

            Section::make('Hero')
                ->schema([
                    TextInput::make('hero_subtitle')
                        ->label('Subtitel boven hero'),
                    TextInput::make('hero_title')
                        ->label('Hero titel')
                        ->required(),
                    CuratorPicker::make('hero_media')
                        ->label('Hero media')
                        ->acceptedFileTypes(['image/*', 'video/*'])
                        ->maxSize(500 * 1024),
                    TextInput::make('hero_duration')
                        ->label('Duur-label')
                        ->helperText('Bijvoorbeeld 0:45'),
                ])->columns(2),

            Section::make('Metadata onder hero')
                ->schema([
                    CuratorPicker::make('intro_logo')
                        ->label('Klein klantlogo')
                        ->acceptedFileTypes(['image/*'])
                        ->maxSize(10 * 1024),
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
                ->description('Slider met afbeeldingen en/of videos. Items behouden hun originele aspect ratio.')
                ->schema([
                    CuratorPicker::make('gallery_items')
                        ->label('Galerij items (afbeelding of video)')
                        ->multiple()
                        ->acceptedFileTypes(['image/*', 'video/*'])
                        ->maxSize(500 * 1024),
                ]),

            Section::make('Groene highlight sectie')
                ->schema([
                    TextInput::make('highlight_title')
                        ->label('Grote titel'),
                    Textarea::make('highlight_body')
                        ->label('Beschrijving')
                        ->rows(5),
                    CuratorPicker::make('highlight_media')
                        ->label('Visual')
                        ->acceptedFileTypes(['image/*', 'video/*'])
                        ->maxSize(500 * 1024),
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
                    CuratorPicker::make('campaign_media')
                        ->label('Grote afbeelding of video')
                        ->acceptedFileTypes(['image/*', 'video/*'])
                        ->maxSize(500 * 1024),
                ])->columns(2),

            Section::make('Storyblok')
                ->description('Full-bleed afbeelding/video met witte tekst-overlay rechts (campagne-style).')
                ->schema([
                    CuratorPicker::make('story_media')
                        ->label('Afbeelding of video')
                        ->acceptedFileTypes(['image/*', 'video/*'])
                        ->maxSize(500 * 1024),
                    TextInput::make('story_title')
                        ->label('Overlay titel'),
                    Textarea::make('story_body')
                        ->label('Overlay beschrijving')
                        ->rows(5),
                ])->columns(2),

            Section::make('Callout (grote tussentitel)')
                ->description('Grote tussentitel zoals "Go,Go,Go Koala!" — gebruik regel-einters voor meerdere regels.')
                ->schema([
                    Textarea::make('callout_title')
                        ->label('Titel')
                        ->rows(3)
                        ->helperText('Toon op aparte regels door enter te gebruiken.'),
                ]),

            Section::make('Tweede storyblok')
                ->description('Grote afbeelding links met beschrijving en CTA-knop rechts.')
                ->schema([
                    CuratorPicker::make('secondary_story_media')
                        ->label('Afbeelding of video')
                        ->acceptedFileTypes(['image/*', 'video/*'])
                        ->maxSize(500 * 1024),
                    Textarea::make('secondary_story_body')
                        ->label('Beschrijving')
                        ->rows(6),
                    TextInput::make('secondary_story_button_text')
                        ->label('CTA-knoptekst')
                        ->helperText('Bijvoorbeeld "Tell us about your project". Leeg = geen knop.'),
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
                    CuratorPicker::make('homepage_video_id')
                        ->label('Homepage video')
                        ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/*'])
                        ->maxSize(500 * 1024),

                    CuratorPicker::make('homepage_photo_id')
                        ->label('Homepage foto')
                        ->acceptedFileTypes(['image/*'])
                        ->maxSize(50 * 1024),
                ])->columns(2),

            Section::make('SEO')
                ->description('Wordt gebruikt voor zoekmachines en social-share-previews.')
                ->collapsed()
                ->schema([
                    TextInput::make('seo_title')
                        ->label('SEO titel')
                        ->maxLength(70)
                        ->helperText('Aanbevolen ~60 tekens. Leeg = de naam van de case wordt gebruikt.'),

                    Textarea::make('meta_description')
                        ->label('Meta description')
                        ->rows(3)
                        ->maxLength(160)
                        ->helperText('Aanbevolen ~155 tekens.'),
                ]),
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

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (CaseStatus $state): string => $state->label())
                    ->color(fn (CaseStatus $state): string => $state === CaseStatus::Published ? 'success' : 'warning'),

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
                Action::make('view')
                    ->label('Bekijken')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn (CaseStudy $record): string => route('cases.show', ['caseStudy' => $record->slug]))
                    ->openUrlInNewTab(),
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
