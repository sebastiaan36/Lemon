<?php

namespace App\Filament\Resources\FloatingPills;

use App\Enums\PillPage;
use App\Filament\Resources\FloatingPills\Pages\CreateFloatingPill;
use App\Filament\Resources\FloatingPills\Pages\EditFloatingPill;
use App\Filament\Resources\FloatingPills\Pages\ListFloatingPills;
use App\Models\FloatingPill;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FloatingPillResource extends Resource
{
    protected static ?string $model = FloatingPill::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCursorArrowRays;

    protected static ?string $navigationLabel = 'Pills';

    protected static ?string $pluralModelLabel = 'Pills';

    protected static ?string $modelLabel = 'Pill';

    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Pill')
                ->description('De zwevende knop rechtsonder in beeld (op mobiel onderin gecentreerd).')
                ->schema([
                    TextInput::make('label')
                        ->label('Tekst')
                        ->required()
                        ->maxLength(60),

                    TextInput::make('href')
                        ->label('Link')
                        ->helperText('Bijv. /contact, #team of een volledige URL.')
                        ->required()
                        ->maxLength(500),

                    Toggle::make('is_active')
                        ->label('Actief')
                        ->default(true),
                ])->columns(3),

            Section::make('Weergave')
                ->description('Op welke pagina en tussen welke secties de pill zichtbaar is. Hij verdwijnt altijd zodra de footer in beeld komt. Overlappen twee pills, dan wint de bovenste in de lijst.')
                ->schema([
                    Select::make('page')
                        ->label('Pagina')
                        ->options(PillPage::options())
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set): void {
                            $set('start_section', null);
                            $set('end_section', null);
                        }),

                    Select::make('start_section')
                        ->label('Vanaf sectie')
                        ->options(fn (Get $get): array => self::sectionOptions($get('page')))
                        ->disabled(fn (Get $get): bool => blank($get('page')))
                        ->required()
                        ->in(fn (Get $get): array => array_keys(self::sectionOptions($get('page')))),

                    Select::make('end_section')
                        ->label('Tot en met sectie')
                        ->options(fn (Get $get): array => self::sectionOptions($get('page')))
                        ->disabled(fn (Get $get): bool => blank($get('page')))
                        ->required()
                        ->in(fn (Get $get): array => array_keys(self::sectionOptions($get('page'))))
                        ->rule(fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                            $order = array_keys(self::sectionOptions($get('page')));
                            $start = array_search($get('start_section'), $order, true);
                            $end = array_search($value, $order, true);

                            if ($start !== false && $end !== false && $end < $start) {
                                $fail('De eindsectie moet op of na de beginsectie liggen.');
                            }
                        }),

                    TextInput::make('desktop_bottom')
                        ->label('Afstand tot onderkant (desktop, px)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(400)
                        ->default(24)
                        ->required(),

                    TextInput::make('sort_order')
                        ->label('Volgorde')
                        ->numeric()
                        ->default(0),
                ])->columns(3),

            Section::make('Avatars')
                ->schema([
                    Toggle::make('use_team_avatars')
                        ->label("Gebruik de teamfoto's van de homepage")
                        ->live(),

                    CuratorPicker::make('avatars')
                        ->label("Foto's of video's (max. 4)")
                        ->helperText('Leeg = standaard avatar.')
                        ->multiple()
                        ->maxItems(4)
                        ->acceptedFileTypes(['image/*', 'video/*'])
                        ->hidden(fn (Get $get): bool => (bool) $get('use_team_avatars')),
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

                TextColumn::make('label')
                    ->label('Tekst')
                    ->searchable(),

                TextColumn::make('page')
                    ->label('Pagina')
                    ->formatStateUsing(fn (PillPage $state): string => $state->label())
                    ->sortable(),

                TextColumn::make('start_section')
                    ->label('Secties')
                    ->formatStateUsing(fn (FloatingPill $record): string => $record->start_section === $record->end_section
                        ? $record->page->sectionLabel($record->start_section)
                        : $record->page->sectionLabel($record->start_section).' → '.$record->page->sectionLabel($record->end_section)),

                TextColumn::make('href')
                    ->label('Link')
                    ->toggleable(),

                ToggleColumn::make('is_active')
                    ->label('Actief'),
            ])
            ->filters([
                SelectFilter::make('page')
                    ->label('Pagina')
                    ->options(PillPage::options()),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->reorderRecordsTriggerAction(fn (Action $action, bool $isReordering): Action => $action
                ->button()
                ->label($isReordering ? 'Klaar met sorteren' : 'Volgorde aanpassen'))
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    public static function sectionOptions(PillPage|string|null $page): array
    {
        $page = $page instanceof PillPage ? $page : PillPage::tryFrom((string) $page);

        return $page?->sections() ?? [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFloatingPills::route('/'),
            'create' => CreateFloatingPill::route('/create'),
            'edit' => EditFloatingPill::route('/{record}/edit'),
        ];
    }
}
