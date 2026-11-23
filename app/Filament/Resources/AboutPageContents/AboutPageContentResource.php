<?php

namespace App\Filament\Resources\AboutPageContents;

use App\Filament\Resources\AboutPageContents\Pages\ManageAboutPageContents;
use App\Models\AboutPageContent;
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
                        ->acceptedFileTypes(['image/*', 'video/*']),
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
                ->description('Grote gele titel links, tekst rechts.')
                ->schema([
                    TextInput::make('credible_title_line1')
                        ->label('Titel regel 1'),

                    TextInput::make('credible_title_line2')
                        ->label('Titel regel 2'),

                    Textarea::make('credible_body')
                        ->label('Bodytekst')
                        ->rows(4),

                ]),

            // ─── Total brand growth ───────────────────────────────────────────
            Section::make('Total brand growth')
                ->description('Titel en tekst links, grote afbeelding rechts.')
                ->schema([
                    TextInput::make('growth_title_line1')
                        ->label('Titel regel 1'),

                    TextInput::make('growth_title_line2')
                        ->label('Titel regel 2'),

                    Textarea::make('growth_body')
                        ->label('Bodytekst')
                        ->rows(6)
                        ->helperText('Lege regel = nieuwe alinea. Zet tekst tussen **dubbele sterretjes** voor vet-cursief.'),

                    CuratorPicker::make('growth_image')
                        ->label('Grote afbeelding (rechts)')
                        ->acceptedFileTypes(['image/*', 'video/*']),

                    TextInput::make('growth_client_name')
                        ->label('Klantnaam op de afbeelding (bijv. Veloretti)'),

                    TagsInput::make('growth_tags')
                        ->label('Tags rechtsonder op de afbeelding (max. 3)')
                        ->placeholder('Voeg tag toe...')
                        ->helperText('Worden als witte pills rechtsonder in de afbeelding weergegeven.'),
                ]),

            // ─── The 4 dimensions ─────────────────────────────────────────────
            Section::make('The 4 dimensions of Total Brand Growth')
                ->description('Gele sectie met "Lemon\'s Total Brand Growth Formula". Teksten in het schema worden automatisch in hoofdletters getoond.')
                ->schema([
                    TextInput::make('dimensions_title')
                        ->label('Titel'),

                    Textarea::make('dimensions_body')
                        ->label('Bodytekst')
                        ->rows(3),

                    TextInput::make('dimensions_formula_title')
                        ->label('Schema: titel'),

                    TextInput::make('dimensions_formula_subtitle')
                        ->label('Schema: ondertitel'),

                    TextInput::make('dimensions_center_title')
                        ->label('Midden (bijv. Brand narrative): titel'),

                    TextInput::make('dimensions_center_subtitle')
                        ->label('Midden: subtitel'),

                    Repeater::make('dimensions_items')
                        ->label('Dimensies (boven, rechts, onder, links: Brand, Employee experience, Proposition, Customer experience)')
                        ->schema([
                            TextInput::make('name')
                                ->label('Naam')
                                ->required(),

                            TextInput::make('subtitle')
                                ->label('Subtitel'),
                        ])
                        ->columns(2)
                        ->minItems(4)
                        ->maxItems(4)
                        ->defaultItems(4)
                        ->reorderable()
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
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
                        ->acceptedFileTypes(['image/*', 'video/*']),
                ]),

            // ─── Our Brand Narrative Approach ─────────────────────────────────
            Section::make('Our Brand Narrative Approach')
                ->schema([
                    TextInput::make('approach_title')
                        ->label('Titel')
                        ->helperText('Een ™ aan het eind wordt klein en hoog weergegeven.'),

                    Textarea::make('approach_body')
                        ->label('Bodytekst')
                        ->rows(6)
                        ->helperText('Lege regel = nieuwe alinea.'),

                    TextInput::make('approach_button_text')
                        ->label('Knoptekst'),

                    TextInput::make('approach_button_href')
                        ->label('Knoplink'),
                ])->columns(2),

            // ─── Your Credible Brand narrative ────────────────────────────────
            Section::make('Your Credible Brand narrative')
                ->description('Titel en tekst links, grote afbeelding rechts.')
                ->schema([
                    TextInput::make('narrative_title')
                        ->label('Titel'),

                    Textarea::make('narrative_body')
                        ->label('Bodytekst')
                        ->rows(4),

                    TextInput::make('narrative_button_text')
                        ->label('Knoptekst'),

                    TextInput::make('narrative_button_href')
                        ->label('Knoplink'),

                    CuratorPicker::make('narrative_image')
                        ->label('Grote afbeelding of video (rechts)')
                        ->acceptedFileTypes(['image/*', 'video/*']),

                    TextInput::make('narrative_client_name')
                        ->label('Klantnaam op de afbeelding'),

                    TagsInput::make('narrative_tags')
                        ->label('Tags rechtsonder op de afbeelding (max. 3)')
                        ->placeholder('Voeg tag toe...'),
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
