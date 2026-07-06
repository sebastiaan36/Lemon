<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Models\AboutPageContent;
use App\Models\ActivityLog;
use App\Models\Blog;
use App\Models\CaseStudy;
use App\Models\ContactPageContent;
use App\Models\CxbyExPageContent;
use App\Models\HomepageContent;
use App\Models\Job;
use App\Models\JobsPageContent;
use App\Models\Media;
use App\Models\User;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Logboek';

    protected static ?string $pluralModelLabel = 'Logboek';

    protected static ?string $modelLabel = 'Logregel';

    protected static \UnitEnum|string|null $navigationGroup = 'Beheer';

    protected static ?int $navigationSort = 1100;

    /**
     * @var array<string, string>
     */
    public const array ACTION_LABELS = [
        'created' => 'Aangemaakt',
        'updated' => 'Gewijzigd',
        'deleted' => 'Verwijderd',
    ];

    /**
     * @var array<class-string, string>
     */
    public const array SUBJECT_LABELS = [
        CaseStudy::class => 'Case',
        Blog::class => 'Blog',
        Job::class => 'Vacature',
        User::class => 'Beheerder',
        Media::class => 'Media',
        HomepageContent::class => 'Homepage',
        AboutPageContent::class => 'Over ons-pagina',
        ContactPageContent::class => 'Contactpagina',
        CxbyExPageContent::class => 'CxbyEx-pagina',
        JobsPageContent::class => 'Vacaturepagina',
    ];

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('created_at')
                    ->label('Datum')
                    ->dateTime('d-m-Y H:i:s'),

                TextEntry::make('user_name')
                    ->label('Beheerder')
                    ->placeholder('Systeem'),

                TextEntry::make('action')
                    ->label('Actie')
                    ->formatStateUsing(fn (string $state): string => self::ACTION_LABELS[$state] ?? $state),

                TextEntry::make('subject_type')
                    ->label('Onderdeel')
                    ->formatStateUsing(fn (string $state): string => self::SUBJECT_LABELS[$state] ?? class_basename($state)),

                TextEntry::make('subject_label')
                    ->label('Item')
                    ->placeholder('—'),

                TextEntry::make('changes')
                    ->label('Wijzigingen')
                    ->listWithLineBreaks()
                    ->state(fn (ActivityLog $record): array => self::describeChanges($record))
                    ->placeholder('Geen veldwijzigingen vastgelegd')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Datum')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),

                TextColumn::make('user_name')
                    ->label('Beheerder')
                    ->placeholder('Systeem')
                    ->searchable(),

                TextColumn::make('action')
                    ->label('Actie')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::ACTION_LABELS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('subject_type')
                    ->label('Onderdeel')
                    ->formatStateUsing(fn (string $state): string => self::SUBJECT_LABELS[$state] ?? class_basename($state)),

                TextColumn::make('subject_label')
                    ->label('Item')
                    ->placeholder('—')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('changes')
                    ->label('Velden')
                    ->state(fn (ActivityLog $record): string => $record->changes
                        ? implode(', ', array_keys($record->changes))
                        : '—')
                    ->limit(60),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('action')
                    ->label('Actie')
                    ->options(self::ACTION_LABELS),

                SelectFilter::make('subject_type')
                    ->label('Onderdeel')
                    ->options(self::SUBJECT_LABELS),

                SelectFilter::make('user_id')
                    ->label('Beheerder')
                    ->relationship('user', 'name'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Details'),
            ]);
    }

    /**
     * @return array<int, string>
     */
    public static function describeChanges(ActivityLog $record): array
    {
        if (! $record->changes) {
            return [];
        }

        return collect($record->changes)
            ->map(function (array $change, string $attribute): string {
                $old = self::stringifyValue($change['old'] ?? null);
                $new = self::stringifyValue($change['new'] ?? null);

                return "{$attribute}: {$old} → {$new}";
            })
            ->values()
            ->all();
    }

    protected static function stringifyValue(mixed $value): string
    {
        return match (true) {
            $value === null => 'leeg',
            $value === true => 'ja',
            $value === false => 'nee',
            is_string($value) && $value === '' => 'leeg',
            default => (string) $value,
        };
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
        ];
    }
}
