<?php

namespace App\Filament\Resources\AboutPageContents;

use App\Filament\Resources\AboutPageContents\Pages\ManageAboutPageContents;
use App\Models\AboutPageContent;
use App\Models\CaseStudy;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AboutPageContentResource extends Resource
{
    protected static ?string $model = AboutPageContent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInformationCircle;

    protected static ?string $navigationLabel = 'About pagina';

    protected static \UnitEnum|string|null $navigationGroup = "Pagina's";

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'About pagina beheren';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            // ─── Hero ─────────────────────────────────────────────────────────
            Section::make('Hero')
                ->description('De gele openingssectie bovenaan de pagina.')
                ->schema([
                    TextInput::make('hero_title')
                        ->label('Titel')
                        ->required()
                        ->maxLength(120),

                    Textarea::make('hero_description')
                        ->label('Beschrijving')
                        ->rows(5),
                ]),

            // ─── Intro quote ──────────────────────────────────────────────────
            Section::make('Intro quote')
                ->description('De grote citaattekst over de achtergrondafbeelding.')
                ->schema([
                    TextInput::make('intro_quote_line1')
                        ->label('Eerste regel (geel)')
                        ->required(),

                    TextInput::make('intro_quote_line2')
                        ->label('Tweede regel (wit)')
                        ->required(),

                    CuratorPicker::make('intro_bg_image')
                        ->label('Achtergrondafbeelding of -video')
                        ->acceptedFileTypes(['image/*', 'video/*'])
                        ->maxSize(500 * 1024),
                ]),

            // ─── CTA button ───────────────────────────────────────────────────
            Section::make('CTA knop')
                ->description('De "Tell us about your project" knop.')
                ->schema([
                    TextInput::make('cta_text')
                        ->label('Knoptekst')
                        ->required(),

                    TextInput::make('cta_href')
                        ->label('Knoplink')
                        ->required(),
                ]),

            // ─── Credible Creativity ─────────────────────────────────────────
            Section::make('Credible Creativity')
                ->description('Donkere sectie met cases-carrousel.')
                ->schema([
                    TextInput::make('credible_title_line1')
                        ->label('Titel regel 1'),

                    TextInput::make('credible_title_line2')
                        ->label('Titel regel 2'),

                    Textarea::make('credible_body')
                        ->label('Bodytekst')
                        ->rows(4),

                    Select::make('credible_cases')
                        ->label('Cases (slider)')
                        ->multiple()
                        ->searchable()
                        ->options(fn (): array => CaseStudy::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->helperText('Selecteer de cases die in de slider worden getoond. De volgorde is gelijk aan de selectievolgorde.'),
                ]),

            // ─── Total brand growth ───────────────────────────────────────────
            Section::make('Total brand growth')
                ->description('Donkere sectie met grote afbeelding links en tekst rechts.')
                ->schema([
                    TextInput::make('growth_title_line1')
                        ->label('Titel regel 1'),

                    TextInput::make('growth_title_line2')
                        ->label('Titel regel 2'),

                    Textarea::make('growth_body')
                        ->label('Bodytekst')
                        ->rows(4),

                    CuratorPicker::make('growth_image')
                        ->label('Grote afbeelding (links)')
                        ->acceptedFileTypes(['image/*', 'video/*'])
                        ->maxSize(500 * 1024),

                    TextInput::make('growth_client_name')
                        ->label('Klantnaam op de afbeelding (bijv. Veloretti)'),

                    TagsInput::make('growth_tags')
                        ->label('Tags rechtsonder op de afbeelding (max. 3)')
                        ->placeholder('Voeg tag toe...')
                        ->helperText('Worden als witte pills rechtsonder in de afbeelding weergegeven.'),
                ]),

            // ─── Services ─────────────────────────────────────────────────────
            Section::make('Services')
                ->description('Donkere sectie met Brand / Experience / Employee.')
                ->schema([
                    Repeater::make('services')
                        ->label('Services')
                        ->schema([
                            TextInput::make('name')
                                ->label('Naam (bijv. Brand)')
                                ->required(),

                            Textarea::make('description')
                                ->label('Beschrijving')
                                ->rows(3)
                                ->required(),

                            TagsInput::make('tags')
                                ->label('Tags (druk Enter na elke tag)')
                                ->placeholder('Voeg tag toe...'),
                        ])
                        ->defaultItems(3)
                        ->addActionLabel('Service toevoegen')
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                ]),

            // ─── We're keeping ancient actual ─────────────────────────────────
            Section::make('"We\'re keeping ancient actual"')
                ->description('Rechts van de services-sectie.')
                ->schema([
                    TextInput::make('ancient_title_line1')
                        ->label('Titel regel 1 (geel)'),

                    TextInput::make('ancient_title_line2')
                        ->label('Titel regel 2 (geel)'),

                    Textarea::make('ancient_body')
                        ->label('Bodytekst')
                        ->rows(4),

                    CuratorPicker::make('ancient_image')
                        ->label('Afbeelding')
                        ->acceptedFileTypes(['image/*', 'video/*'])
                        ->maxSize(500 * 1024),
                ]),

            // ─── International community ──────────────────────────────────────
            Section::make('International community')
                ->description('Lichte sectie met locatietekst.')
                ->schema([
                    TextInput::make('international_title_line1')
                        ->label('Titel regel 1'),

                    TextInput::make('international_title_line2')
                        ->label('Titel regel 2'),

                    Textarea::make('international_body')
                        ->label('Bodytekst')
                        ->rows(6),
                ]),

            // ─── Team foto's ──────────────────────────────────────────────────
            Section::make('Team foto\'s')
                ->description('De gepolariseerde team- of sfeerfotos onderaan.')
                ->schema([
                    CuratorPicker::make('team_photos')
                        ->label('Foto\'s (max. 5)')
                        ->multiple()
                        ->maxItems(5)
                        ->acceptedFileTypes(['image/*', 'video/*'])
                        ->maxSize(500 * 1024)
                        ->helperText('Worden schuin weergegeven in polaroid-stijl.'),
                ]),

            // ─── Join our team ────────────────────────────────────────────────
            Section::make('"Join our team" knop')
                ->schema([
                    TextInput::make('join_team_text')
                        ->label('Knoptekst'),

                    TextInput::make('join_team_href')
                        ->label('Knoplink'),
                ]),

            // ─── SEO ──────────────────────────────────────────────────────────
            Section::make('SEO')
                ->description('Wordt gebruikt voor zoekmachines en social-share-previews.')
                ->collapsed()
                ->schema([
                    TextInput::make('seo_title')
                        ->label('SEO titel')
                        ->maxLength(70)
                        ->helperText('Aanbevolen ~60 tekens. Leeg = standaard pagina-titel.'),

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
        return $table->columns([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAboutPageContents::route('/'),
        ];
    }
}
