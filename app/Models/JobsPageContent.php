<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'hero_bg_image',
    'hero_title',
    'hero_subtitle',
    'seo_title',
    'meta_description',
])]
class JobsPageContent extends Model
{
    public static function getSingleton(): self
    {
        return self::firstOrCreate([], [
            'hero_title' => 'Join Lemon',
            'hero_subtitle' => "It's an open door, but our door is open to everyone.",
        ]);
    }
}
