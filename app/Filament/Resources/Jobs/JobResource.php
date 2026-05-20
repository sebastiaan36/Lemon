<?php

namespace App\Filament\Resources\Jobs;

use App\Enums\CaseStatus;
use App\Filament\Resources\Jobs\Pages\CreateJob;
use App\Filament\Resources\Jobs\Pages\EditJob;
use App\Filament\Resources\Jobs\Pages\ListJobs;
use App\Models\Job;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\RichEditor;
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

class JobResource extends Resource
{
    protected static ?string $model = Job::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?string $navigationLabel = 'Jobs';

    protected static ?string $pluralModelLabel = 'Jobs';

    protected static ?string $modelLabel = 'Job';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Algemeen')
                ->schema([
                    TextInput::make('title')
                        ->label('Functietitel (in menu)')
                        ->required()
                        ->maxLength(200)
                        ->live(onBlur: true),

                    TextInput::make('job_title')
                        ->label('Vacaturetitel (als header op de pagina)')
                        ->maxLength(300)
                        ->columnSpanFull(),

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

                    TagsInput::make('tags')
                        ->label('Tags (bv. Fulltime, Amsterdam, €300-€500)')
                        ->placeholder('Voeg een tag toe')
                        ->columnSpanFull(),

                    Textarea::make('short_description')
                        ->label('Korte beschrijving (intro)')
                        ->rows(3)
                        ->columnSpanFull(),

                    RichEditor::make('body')
                        ->label('Vacaturetekst')
                        ->toolbarButtons([
                            'bold', 'italic', 'underline',
                            'bulletList', 'orderedList',
                            'h2', 'h3',
                            'link',
                            'undo', 'redo',
                        ])
                        ->columnSpanFull(),
                ])->columns(3),

            Section::make('Sollicitatie')
                ->schema([
                    TextInput::make('apply_button_text')
                        ->label('Knoptekst')
                        ->default('Want this job?')
                        ->maxLength(60),

                    TextInput::make('apply_email')
                        ->label('E-mailadres sollicitatie')
                        ->email()
                        ->maxLength(200),

                    TextInput::make('apply_contact_name')
                        ->label('Contactpersoon (bv. "Mail Kirsten")')
                        ->maxLength(100),

                    CuratorPicker::make('apply_contact_photo')
                        ->label('Foto contactpersoon')
                        ->acceptedFileTypes(['image/*']),
                ])->columns(3),
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

                TextColumn::make('title')
                    ->label('Functietitel')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (CaseStatus $state): string => match ($state) {
                        CaseStatus::Published => 'success',
                        CaseStatus::Concept => 'warning',
                    })
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
            'index' => ListJobs::route('/'),
            'create' => CreateJob::route('/create'),
            'edit' => EditJob::route('/{record}/edit'),
        ];
    }
}
