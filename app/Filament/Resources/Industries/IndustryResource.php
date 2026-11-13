<?php

namespace App\Filament\Resources\Industries;

use App\Enums\CaseStatus;
use App\Filament\Resources\Industries\Pages\CreateIndustry;
use App\Filament\Resources\Industries\Pages\EditIndustry;
use App\Filament\Resources\Industries\Pages\ListIndustries;
use App\Models\CaseStudy;
use App\Models\Industry;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IndustryResource extends Resource
{
    protected static ?string $model = Industry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationLabel = 'Industries';

    protected static ?string $pluralModelLabel = 'Industries';

    protected static ?string $modelLabel = 'Industry';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Algemeen')
                ->schema([
                    TextInput::make('name')
                        ->label('Naam (titel links en in de navigatie)')
                        ->required()
                        ->maxLength(80),

                    TextInput::make('slug')
                        ->label('Slug (anker, bijv. /industries#aviation)')
                        ->maxLength(80)
                        ->helperText('Leeg laten = automatisch op basis van de naam.'),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            CaseStatus::Concept->value => 'Concept',
                            CaseStatus::Published->value => 'Gepubliceerd',
                        ])
                        ->default(CaseStatus::Concept->value)
                        ->required(),

                    TextInput::make('sort_order')
                        ->label('Volgorde')
                        ->numeric()
                        ->default(0),

                    Textarea::make('headline')
                        ->label('Kop (groot, wit)')
                        ->rows(2)
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make('Why …')
                ->schema([
                    TextInput::make('why_title')
                        ->label('Titel (geel)')
                        ->maxLength(160),

                    Textarea::make('why_lead')
                        ->label('Intro (vet-cursief)')
                        ->rows(2),

                    Textarea::make('why_body')
                        ->label('Tekst')
                        ->rows(6),
                ]),

            Section::make('Cases')
                ->schema([
                    Select::make('cases')
                        ->label('Cases (carrousel)')
                        ->multiple()
                        ->searchable()
                        ->options(fn (): array => CaseStudy::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->helperText('Volgorde = selectievolgorde. Conceptcases worden voor bezoekers verborgen.'),
                ]),

            Section::make('Our approach')
                ->schema([
                    TextInput::make('approach_title')
                        ->label('Titel (geel)')
                        ->maxLength(80),

                    Repeater::make('approach_items')
                        ->label('Stappen (worden automatisch genummerd)')
                        ->schema([
                            TextInput::make('title')
                                ->label('Titel')
                                ->required()
                                ->maxLength(120),

                            TextInput::make('subtitle')
                                ->label('Subtitel (geel)')
                                ->maxLength(200),

                            Textarea::make('body')
                                ->label('Tekst')
                                ->rows(3)
                                ->columnSpanFull(),

                            TagsInput::make('bullets')
                                ->label('Opsommingspunten (Enter na elk punt)')
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                ]),

            Section::make('What we believe')
                ->schema([
                    TextInput::make('belief_title')
                        ->label('Titel (geel)')
                        ->maxLength(80),

                    Textarea::make('belief_body')
                        ->label('Tekst (groot, cursief)')
                        ->rows(3),

                    TextInput::make('belief_highlight')
                        ->label('Slotzin (groot, geel)')
                        ->maxLength(160),
                ]),

            Section::make('Geel CTA-blok')
                ->schema([
                    Textarea::make('cta_text')
                        ->label('Tekst')
                        ->rows(4)
                        ->columnSpanFull(),

                    TextInput::make('cta_primary_text')
                        ->label('Zwarte knop: tekst'),

                    TextInput::make('cta_primary_href')
                        ->label('Zwarte knop: link'),

                    TextInput::make('cta_secondary_text')
                        ->label('Rand-knop: tekst'),

                    TextInput::make('cta_secondary_href')
                        ->label('Rand-knop: link'),
                ])->columns(2),

            Section::make('Klantlogo\'s')
                ->schema([
                    TextInput::make('proof_title')
                        ->label('Titel (geel)')
                        ->maxLength(80),

                    Repeater::make('proof_logos')
                        ->label('Logo\'s (wit op zwart)')
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
                        ->itemLabel(fn (array $state): string => $state['name'] ?? 'Logo'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width(50),

                TextColumn::make('name')
                    ->label('Naam')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (CaseStatus $state): string => $state === CaseStatus::Published ? 'success' : 'warning')
                    ->formatStateUsing(fn (CaseStatus $state): string => $state->label()),

                TextColumn::make('updated_at')
                    ->label('Bijgewerkt')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIndustries::route('/'),
            'create' => CreateIndustry::route('/create'),
            'edit' => EditIndustry::route('/{record}/edit'),
        ];
    }
}
