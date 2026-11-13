<?php

namespace App\Models;

use App\Enums\CaseStatus;
use App\Models\Concerns\HasUniqueSlug;
use App\Models\Concerns\LogsActivity;
use Database\Factories\IndustryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable([
    'name',
    'slug',
    'status',
    'sort_order',
    'headline',
    'why_title',
    'why_lead',
    'why_body',
    'cases',
    'approach_title',
    'approach_items',
    'belief_title',
    'belief_body',
    'belief_highlight',
    'cta_text',
    'cta_primary_text',
    'cta_primary_href',
    'cta_secondary_text',
    'cta_secondary_href',
    'proof_title',
    'proof_logos',
])]
class Industry extends Model
{
    /** @use HasFactory<IndustryFactory> */
    use HasFactory;

    use HasUniqueSlug;
    use LogsActivity;

    public const MENU_CACHE_KEY = 'site-header.industries';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::MENU_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::MENU_CACHE_KEY));
    }

    protected function casts(): array
    {
        return [
            'status' => CaseStatus::class,
            'sort_order' => 'integer',
            'cases' => 'array',
            'approach_items' => 'array',
            'proof_logos' => 'array',
        ];
    }

    protected function slugSource(): string
    {
        return 'name';
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', CaseStatus::Published);
    }
}
