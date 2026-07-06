<?php

namespace App\Filament\Curator\Pages;

use App\Filament\Curator\MediaResource;
use Awcodes\Curator\Resources\Media\Pages\EditMedia as BaseEditMedia;

class EditMedia extends BaseEditMedia
{
    protected static string $resource = MediaResource::class;
}
