<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'hero_title',
    'hero_bg_image',
    'hero_address',
    'hero_phone',
    'hero_email',
    'intro_text',
    'team_members',
    'client_logos_label',
    'client_logos',
    'join_intro_text',
    'join_title_line1',
    'join_title_line2',
    'join_jobs',
    'join_button_text',
    'join_button_href',
    'seo_title',
    'meta_description',
])]
class ContactPageContent extends Model
{
    use LogsActivity;

    protected function casts(): array
    {
        return [
            'team_members' => 'array',
            'client_logos' => 'array',
            'join_jobs' => 'array',
        ];
    }

    public static function getSingleton(): self
    {
        return self::firstOrCreate([], [
            'hero_title' => 'Get in touch',
            'hero_address' => 'Korte Prinsengracht 26, 1013 GS Amsterdam',
            'hero_phone' => '+31 (0) 20 606 3580',
            'hero_email' => 'info@lemonscentedtea.com',
            'intro_text' => 'We partner with leadership teams to transform brand ambition into lived behavior turning culture into a competitive advantage.',
            'team_members' => [],
            'client_logos_label' => 'These brands called us before',
            'client_logos' => [],
            'join_intro_text' => 'We are always looking for new talent',
            'join_title_line1' => 'Join',
            'join_title_line2' => 'lemon',
            'join_jobs' => [],
            'join_button_text' => 'All jobs',
            'join_button_href' => null,
        ]);
    }
}
