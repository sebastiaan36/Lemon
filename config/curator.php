<?php

declare(strict_types=1);
use App\Filament\Curator\MediaResource;
use App\Filament\Curator\MediaTable;
use App\Filament\Curator\Pages\CreateMedia;
use App\Filament\Curator\Pages\EditMedia;
use App\Filament\Curator\Pages\ListMedia;
use App\Models\Media;
use Awcodes\Curator\Enums\PreviewableExtensions;
use Awcodes\Curator\Providers\GlideUrlProvider;
use Awcodes\Curator\Resources\Media\Schemas\MediaForm;

return [
    'curation_formats' => PreviewableExtensions::toArray(),
    'default_disk' => env('CURATOR_DEFAULT_DISK', 'public'),
    'default_directory' => null,
    'default_visibility' => 'public',
    'features' => [
        'curations' => true,
        'file_swap' => true,
        'directory_restriction' => false,
        'preserve_file_names' => true,
        'tenancy' => [
            'enabled' => false,
            'relationship_name' => null,
        ],
    ],
    'glide_token' => env('CURATOR_GLIDE_TOKEN'),
    'model' => Media::class,
    'path_generator' => null,
    'resource' => [
        'label' => 'Media',
        'plural_label' => 'Media',
        'default_layout' => 'grid',
        'navigation' => [
            'group' => null,
            'icon' => 'heroicon-o-photo',
            'sort' => null,
            'should_register' => true,
            'should_show_badge' => false,
        ],
        'resource' => MediaResource::class,
        'pages' => [
            'create' => CreateMedia::class,
            'edit' => EditMedia::class,
            'index' => ListMedia::class,
        ],
        'schemas' => [
            'form' => MediaForm::class,
        ],
        'tables' => [
            'table' => MediaTable::class,
        ],
    ],
    'url_provider' => GlideUrlProvider::class,
];
