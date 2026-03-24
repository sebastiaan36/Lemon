<?php

namespace App\Filament\Resources\HomepageContents;

use App\Filament\Resources\HomepageContents\Pages\ManageHomepageContents;
use App\Models\HomepageContent;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
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

class HomepageContentResource extends Resource
{
    protected static ?string $model = HomepageContent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static ?string $navigationLabel = 'Homepage';

    protected static ?string $title = 'Homepage beheren';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            // ─── Hero sectie ────────────────────────────────────────────────
            Section::make('Hero')
                ->description('De openingsanimatie bovenaan de pagina.')
                ->columns(1)
                ->schema([
                    TextInput::make('hero_title')
                        ->label('Titel')
                        ->required()
                        ->maxLength(120),

                    Textarea::make('hero_body_text')
                        ->label('Bodytekst (scrollt door het beeld)')
                        ->rows(6),

                    FileUpload::make('hero_bg_image')
                        ->label('Hoofdvideo of afbeelding (kaart 0 — expandeert naar volledig scherm)')
                        ->disk('public')
                        ->directory('homepage/hero')
                        ->acceptedFileTypes(['image/*', 'video/*'])
                        ->helperText('Wordt uitvergroot van kleine kaart naar volledig scherm. Ondersteunt video (.mp4/.webm) en afbeelding.'),

                    Repeater::make('hero_floating_images')
                        ->label('Zwevende kaarten (max. 4) — scatter bij scroll')
                        ->schema([
                            FileUpload::make('image')
                                ->label('Video of afbeelding')
                                ->disk('public')
                                ->directory('homepage/floating')
                                ->acceptedFileTypes(['image/*', 'video/*'])
                                ->helperText('Ondersteunt video (.mp4/.webm) en afbeelding.')
                                ->required(),
                        ])
                        ->maxItems(4)
                        ->addActionLabel('Kaart toevoegen')
                        ->collapsible()
                        ->helperText('De 4 kaarten die rondom de hoofdkaart zweven en bij scroll wegvliegen.'),
                ]),

            // ─── Services intro ──────────────────────────────────────────────
            Section::make('Services intro')
                ->description('De introductietekst op de gele achtergrond.')
                ->schema([
                    Textarea::make('services_intro_text')
                        ->label('Introductietekst')
                        ->rows(3),
                ]),

            // ─── Services (Brand / Experience / Employee) ────────────────────
            Section::make('Services')
                ->description('De drie services met media, beschrijving en tags.')
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

                            Select::make('mediaType')
                                ->label('Media type')
                                ->options([
                                    'image' => 'Afbeelding',
                                    'video' => 'Video',
                                ])
                                ->default('image'),

                            FileUpload::make('mediaUrl')
                                ->label('Afbeelding of video')
                                ->disk('public')
                                ->directory('homepage/services')
                                ->acceptedFileTypes(['image/*', 'video/*'])
                                ->helperText('Upload een afbeelding (.jpg/.png/.webp) of video (.mp4/.webm).')
                                ->live()
                                ->afterStateUpdated(function (callable $set, $state): void {
                                    if (! $state) {
                                        return;
                                    }
                                    $filename = is_array($state) ? (string) array_key_first($state) : (string) $state;
                                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                    $set('mediaType', in_array($ext, ['mp4', 'webm', 'mov', 'avi']) ? 'video' : 'image');
                                }),
                        ])
                        ->defaultItems(3)
                        ->addActionLabel('Service toevoegen')
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                ]),

            // ─── Team-avatars ─────────────────────────────────────────────────
            Section::make('Meet the team')
                ->description('De avatar-foto\'s in de "Meet the team" pill.')
                ->schema([
                    Repeater::make('team_avatars')
                        ->label('Teamfoto\'s of -video\'s')
                        ->schema([
                            FileUpload::make('avatar')
                                ->label('Foto of video')
                                ->disk('public')
                                ->directory('homepage/team')
                                ->acceptedFileTypes(['image/*', 'video/*'])
                                ->helperText('Ondersteunt foto (.jpg/.png/.webp) en video (.mp4/.webm).')
                                ->required(),
                        ])
                        ->maxItems(6)
                        ->addActionLabel('Foto of video toevoegen')
                        ->collapsible(),
                ]),

            // ─── Cases ────────────────────────────────────────────────────────
            Section::make('Cases')
                ->description('De cases in de horizontaal scrollende carousel.')
                ->schema([
                    Repeater::make('cases')
                        ->label('Cases')
                        ->schema([
                            TextInput::make('name')
                                ->label('Naam')
                                ->required(),

                            FileUpload::make('image')
                                ->label('Afbeelding')
                                ->image()
                                ->disk('public')
                                ->directory('homepage/cases'),
                        ])
                        ->defaultItems(0)
                        ->addActionLabel('Case toevoegen')
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
                ]),

            // ─── Testimonial ──────────────────────────────────────────────────
            Section::make('Testimonial')
                ->description('Het quote-blok met klantgetuigenis.')
                ->schema([
                    Textarea::make('testimonial_quote')
                        ->label('Quote')
                        ->rows(4),

                    TextInput::make('testimonial_author')
                        ->label('Naam auteur (bijv. Michelle)'),

                    FileUpload::make('testimonial_author_avatar')
                        ->label('Avatar auteur')
                        ->image()
                        ->disk('public')
                        ->directory('homepage/testimonial'),

                    FileUpload::make('testimonial_logo')
                        ->label('Logo bedrijf (bijv. Delta)')
                        ->image()
                        ->disk('public')
                        ->directory('homepage/testimonial'),
                ]),

            // ─── Client logos ─────────────────────────────────────────────────
            Section::make('Klantlogo\'s')
                ->description('De rij met klantlogo\'s onder het testimonial-blok.')
                ->schema([
                    Repeater::make('client_logos')
                        ->label('Logo\'s')
                        ->schema([
                            FileUpload::make('logo')
                                ->label('Logo')
                                ->image()
                                ->disk('public')
                                ->directory('homepage/clients')
                                ->required(),
                        ])
                        ->defaultItems(0)
                        ->addActionLabel('Logo toevoegen')
                        ->collapsible(),
                ]),

            // ─── Team section ─────────────────────────────────────────────────
            Section::make('Team sectie')
                ->description('De "Team Lemon" sectie met beschrijving en foto\'s of video\'s.')
                ->schema([
                    Textarea::make('team_description')
                        ->label('Beschrijving')
                        ->rows(4),

                    TextInput::make('team_button_text')
                        ->label('Knoptekst')
                        ->default('About us'),

                    TextInput::make('team_button_href')
                        ->label('Knoplink')
                        ->default('#about')
                        ->helperText('Bijvoorbeeld #about of /about'),

                    Repeater::make('team_photos')
                        ->label('Team foto\'s of video\'s (polaroid-stijl)')
                        ->schema([
                            FileUpload::make('photo')
                                ->label('Foto of video')
                                ->disk('public')
                                ->directory('homepage/team-photos')
                                ->acceptedFileTypes(['image/*', 'video/*'])
                                ->helperText('Ondersteunt foto (.jpg/.png/.webp) en video (.mp4/.webm).')
                                ->required(),
                        ])
                        ->maxItems(5)
                        ->defaultItems(0)
                        ->addActionLabel('Foto of video toevoegen')
                        ->collapsible(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        // Nooit gebruikt — de resource stuurt altijd door naar de edit-pagina
        return $table->columns([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageHomepageContents::route('/'),
        ];
    }
}
