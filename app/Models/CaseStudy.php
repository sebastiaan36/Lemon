<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'slug',
    'client_name',
    'accent_color',
    'hero_title',
    'hero_subtitle',
    'hero_media',
    'hero_duration',
    'intro_logo',
    'touchpoints',
    'overview_title',
    'overview_body',
    'gallery_items',
    'highlight_title',
    'highlight_body',
    'highlight_media',
    'highlight_button_text',
    'campaign_title',
    'campaign_body',
    'campaign_media',
    'story_title',
    'story_body',
    'story_images',
    'results_heading',
    'results_stats',
    'optional_panel_title',
    'optional_panel_body',
    'optional_panel_button_text',
    'video',
    'photo',
    'is_featured',
    'sort_order',
])]
class CaseStudy extends Model
{
    /** @use HasFactory<\Database\Factories\CaseStudyFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (self $caseStudy): void {
            if (! $caseStudy->slug || $caseStudy->isDirty('name')) {
                $caseStudy->slug = $caseStudy->generateUniqueSlug($caseStudy->slug ?: $caseStudy->name);
            }

            $caseStudy->client_name ??= $caseStudy->name;
            $caseStudy->hero_title ??= $caseStudy->name;
            $caseStudy->hero_subtitle ??= $caseStudy->client_name;
            $caseStudy->hero_media ??= $caseStudy->video ?: $caseStudy->photo;
            $caseStudy->accent_color ??= '#0A7949';
            $caseStudy->results_heading ??= 'Results';
        });
    }

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'touchpoints' => 'array',
            'gallery_items' => 'array',
            'story_images' => 'array',
            'results_stats' => 'array',
        ];
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true)->orderBy('sort_order');
    }

    protected function generateUniqueSlug(string $value): string
    {
        $baseSlug = Str::slug($value) ?: 'case';
        $slug = $baseSlug;
        $suffix = 1;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($this->exists, fn (Builder $query) => $query->whereKeyNot($this->getKey()))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
