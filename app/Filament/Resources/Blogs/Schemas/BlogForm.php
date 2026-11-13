<?php

namespace App\Filament\Resources\Blogs\Schemas;

use App\Enums\CaseStatus;
use App\Models\Blog;
use Awcodes\Curator\Components\Forms\CuratorPicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BlogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Blog')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titel')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (callable $set, ?string $state, string $operation): void {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug($state ?? ''));
                                }
                            }),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('category')
                            ->label('Categorie')
                            ->helperText('Wordt gebruikt voor het filter op de blogpagina.')
                            ->maxLength(60)
                            ->datalist(fn (): array => Blog::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category')->all()),

                        TagsInput::make('tags')
                            ->label('Tags')
                            ->helperText('Extra labels naast de categorie, bijv. de branche.')
                            ->reorderable(),

                        Select::make('status')
                            ->label('Status')
                            ->options(collect(CaseStatus::cases())->mapWithKeys(fn (CaseStatus $status): array => [$status->value => $status->label()])->all())
                            ->default(CaseStatus::Concept->value)
                            ->required(),

                        DateTimePicker::make('published_at')
                            ->label('Publicatiedatum')
                            ->seconds(false),

                        CuratorPicker::make('header_photo')
                            ->label('Header foto')
                            ->acceptedFileTypes(['image/*'])
                            ->maxSize(50 * 1024)
                            ->columnSpanFull(),

                        Textarea::make('intro')
                            ->label('Intro')
                            ->helperText('Grote openingstekst boven de content.')
                            ->rows(3)
                            ->maxLength(600)
                            ->columnSpanFull(),

                        RichEditor::make('content')
                            ->label('Content')
                            ->toolbarButtons([
                                ['bold', 'italic', 'underline', 'strike', 'link'],
                                ['h1', 'h2', 'h3', 'paragraph'],
                                ['alignStart', 'alignCenter', 'alignEnd'],
                                ['blockquote', 'bulletList', 'orderedList'],
                                ['attachFiles', 'undo', 'redo'],
                            ])
                            ->fileAttachmentsDisk('public')
                            ->fileAttachmentsDirectory('blog-content')
                            ->fileAttachmentsVisibility('public')
                            ->fileAttachmentsAcceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/gif'])
                            ->helperText('Inline afbeeldingen: PNG, JPG, WebP of GIF. Gebruik de mediabibliotheek voor SVG.')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Auteur')
                    ->description('Wordt naast de titel getoond en bij citaten (blockquote) in de content.')
                    ->schema([
                        TextInput::make('author_name')
                            ->label('Naam')
                            ->maxLength(120),

                        CuratorPicker::make('author_photo')
                            ->label('Foto')
                            ->acceptedFileTypes(['image/*'])
                            ->maxSize(10 * 1024),
                    ])->columns(2),

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
}
