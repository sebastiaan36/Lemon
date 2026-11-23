<?php

namespace App\Filament\Resources\IndustriesPageContents;

use App\Enums\CaseStatus;
use App\Filament\Resources\Industries\IndustryResource;
use App\Filament\Resources\IndustriesPageContents\Pages\ManageIndustriesPageContents;
use App\Models\IndustriesPageContent;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IndustriesPageContentResource extends Resource
{
    protected static ?string $model = IndustriesPageContent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationLabel = 'Industries pagina';

    protected static \UnitEnum|string|null $navigationGroup = "Pagina's";

    protected static ?int $navigationSort = 6;

    protected static ?string $title = 'Industries pagina beheren';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Hero')
                ->description('De gele opening bovenaan de pagina.')
                ->schema([
                    TextInput::make('hero_title')
                        ->label('Titel')
                        ->required()
                        ->maxLength(80),

                    Textarea::make('hero_description')
                        ->label('Introtekst (vervaagt naar onder)')
                        ->rows(5),

                    TextInput::make('nav_label')
                        ->label('Label in de branche-navigatie (geel)')
                        ->maxLength(40),
                ]),

            Section::make('Branches')
                ->description('Alle branches op de pagina, in deze volgorde (sleep om te herschikken). Conceptbranches zijn alleen zichtbaar voor ingelogde beheerders.')
                ->schema([
                    Repeater::make('industries')
                        ->hiddenLabel()
                        ->schema([
                            Hidden::make('id'),
                            ...IndustryResource::industryFields(withSortOrder: false),
                        ])
                        ->reorderable()
                        ->collapsible()
                        ->collapsed()
                        ->cloneable(false)
                        ->addActionLabel('Branche toevoegen')
                        ->deleteAction(fn (Action $action): Action => $action
                            ->requiresConfirmation()
                            ->modalHeading('Branche verwijderen?')
                            ->modalDescription('De branche wordt na opslaan definitief verwijderd.'))
                        ->itemLabel(function (array $state): string {
                            $name = $state['name'] ?? null ?: 'Nieuwe branche';
                            $status = $state['status'] ?? null;
                            $isConcept = ($status instanceof CaseStatus ? $status->value : $status) !== CaseStatus::Published->value;

                            return $isConcept ? $name.' (concept)' : $name;
                        }),
                ]),

            Section::make('Contactknop in de gele CTA-blokken')
                ->schema([
                    CuratorPicker::make('cta_avatar')
                        ->label('Avatar in de zwarte knop')
                        ->acceptedFileTypes(['image/*'])
                        ->helperText('Leeg laten = de huidige standaardfoto.'),
                ]),

            Section::make('SEO')
                ->collapsed()
                ->schema([
                    TextInput::make('seo_title')
                        ->label('SEO titel')
                        ->maxLength(70),

                    Textarea::make('meta_description')
                        ->label('Meta description')
                        ->rows(3)
                        ->maxLength(160),
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
            'index' => ManageIndustriesPageContents::route('/'),
        ];
    }
}
