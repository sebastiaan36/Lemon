<?php

namespace App\Filament\Resources\Blogs\Tables;

use App\Enums\CaseStatus;
use App\Models\Blog;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BlogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titel')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (CaseStatus $state): string => $state->label())
                    ->color(fn (CaseStatus $state): string => $state === CaseStatus::Published ? 'success' : 'warning'),

                TextColumn::make('published_at')
                    ->label('Publicatie')
                    ->dateTime('d-m-Y H:i')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Bijgewerkt')
                    ->dateTime('d-m-Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->defaultSort('published_at', 'desc')
            ->recordActions([
                Action::make('view')
                    ->label('Bekijken')
                    ->icon(Heroicon::OutlinedEye)
                    ->url(fn (Blog $record): string => route('blog.show', ['blog' => $record->slug]))
                    ->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
