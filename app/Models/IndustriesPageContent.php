<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'hero_title',
    'hero_description',
    'nav_label',
    'cta_avatar',
    'seo_title',
    'meta_description',
])]
class IndustriesPageContent extends Model
{
    use LogsActivity;

    public static function getSingleton(): self
    {
        return self::firstOrCreate([], [
            'hero_title' => 'Industries',
            'hero_description' => "Every industry has its own challenges. But the strongest brands have one thing in common: they connect with people.\nWe combine deep industry understanding with Brand, CX and EX expertise to turn strategy into experiences that are felt, lived and remembered.",
            'nav_label' => 'Our industries',
        ]);
    }
}
