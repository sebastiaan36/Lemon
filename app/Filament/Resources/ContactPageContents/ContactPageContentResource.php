<?php

namespace App\Filament\Resources\ContactPageContents;

use App\Filament\Resources\ContactPageContents\Pages\ManageContactPageContents;
use App\Models\ContactPageContent;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContactPageContentResource extends Resource
{
    protected static ?string $model = ContactPageContent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Contact pagina';

    protected static \UnitEnum|string|null $navigationGroup = "Pagina's";

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Contact pagina beheren';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            // ─── Hero ─────────────────────────────────────────────────────────
            Section::make('Hero')
                ->description('De openingssectie met achtergrondafbeelding.')
                ->schema([
                    TextInput::make('hero_title')
                        ->label('Titel')
                        ->required()
                        ->maxLength(100),

                    CuratorPicker::make('hero_bg_image')
                        ->label('Achtergrondafbeelding')
                        ->acceptedFileTypes(['image/*', 'video/*'])
                        ->maxSize(500 * 1024),

                    TextInput::make('hero_address')
                        ->label('Adres')
                        ->maxLength(200),

                    TextInput::make('hero_phone')
                        ->label('Telefoonnummer')
                        ->maxLength(50),

                    TextInput::make('hero_email')
                        ->label('E-mailadres')
                        ->email()
                        ->maxLength(200),
                ]),

            // ─── Intro ────────────────────────────────────────────────────────
            Section::make('Intro tekst')
                ->description('De bodytekst op de gele achtergrond.')
                ->schema([
                    Textarea::make('intro_text')
                        ->label('Tekst')
                        ->rows(4),
                ]),

            // ─── Team ─────────────────────────────────────────────────────────
            Section::make('Meet the Lemons')
                ->description('Teamleden die in de carrousel worden getoond.')
                ->schema([
                    Repeater::make('team_members')
                        ->label('Teamleden')
                        ->schema([
                            TextInput::make('name')
                                ->label('Naam')
                                ->required(),

                            TextInput::make('role')
                                ->label('Functie')
                                ->required(),

                            TextInput::make('phone')
                                ->label('Telefoonnummer')
                                ->maxLength(50),

                            TextInput::make('email')
                                ->label('E-mailadres')
                                ->email()
                                ->maxLength(200),

                            CuratorPicker::make('photo')
                                ->label('Foto')
                                ->acceptedFileTypes(['image/*'])
                                ->maxSize(10 * 1024),
                        ])
                        ->columns(3)
                        ->reorderable()
                        ->collapsible(),
                ]),

            // ─── Client logos ─────────────────────────────────────────────────
            Section::make('Klantlogo\'s')
                ->description('Logo\'s van klanten in de logo-strip.')
                ->schema([
                    TextInput::make('client_logos_label')
                        ->label('Label boven de logo\'s')
                        ->maxLength(100),

                    CuratorPicker::make('client_logos')
                        ->label('Logo\'s')
                        ->acceptedFileTypes(['image/*', 'image/svg+xml'])
                        ->maxSize(50 * 1024)
                        ->multiple(),
                ]),

            // ─── Join section ─────────────────────────────────────────────────
            Section::make('Join sectie')
                ->description('De zwarte "Join lemon" sectie onderaan.')
                ->schema([
                    TextInput::make('join_intro_text')
                        ->label('Intro label')
                        ->maxLength(120),

                    TextInput::make('join_title_line1')
                        ->label('Titel regel 1 (wit)')
                        ->maxLength(60),

                    TextInput::make('join_title_line2')
                        ->label('Titel regel 2 (geel)')
                        ->maxLength(60),

                    Repeater::make('join_jobs')
                        ->label('Vacatures')
                        ->schema([
                            TextInput::make('title')
                                ->label('Functietitel')
                                ->required(),

                            Toggle::make('is_featured')
                                ->label('Uitgelicht (wit, grote tekst)')
                                ->default(false),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible(),

                    TextInput::make('join_button_text')
                        ->label('Knoptekst')
                        ->maxLength(60),

                    TextInput::make('join_button_href')
                        ->label('Knoplink')
                        ->maxLength(200),
                ]),

            // ─── SEO ──────────────────────────────────────────────────────────
            Section::make('SEO')
                ->schema([
                    TextInput::make('seo_title')
                        ->label('Paginatitel (browser tab)')
                        ->maxLength(120),

                    Textarea::make('meta_description')
                        ->label('Meta omschrijving')
                        ->rows(3)
                        ->maxLength(300),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageContactPageContents::route('/'),
        ];
    }
}
