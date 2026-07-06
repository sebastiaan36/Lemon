<?php

namespace App\Models;

use App\Enums\CaseStatus;
use App\Models\Concerns\LogsActivity;
use Database\Factories\CaseStudyFactory;
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
    'style_variant',
    'hero_title',
    'hero_title_line_1',
    'hero_title_line_2',
    'hero_title_line_3',
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
    'story_media',
    'pre_callout_title',
    'pre_callout_body',
    'pre_callout_gallery_items',
    'story_images',
    'callout_title',
    'secondary_story_body',
    'secondary_story_media',
    'secondary_story_button_text',
    'results_heading',
    'results_stats',
    'optional_panel_title',
    'optional_panel_body',
    'optional_panel_button_text',
    'is_featured',
    'sort_order',
    'status',
    'homepage_video_id',
    'homepage_photo_id',
    'autoplay_video',
    'seo_title',
    'meta_description',
])]
class CaseStudy extends Model
{
    /** @use HasFactory<CaseStudyFactory> */
    use HasFactory;

    use LogsActivity;

    protected static function booted(): void
    {
        static::saving(function (self $caseStudy): void {
            if (! $caseStudy->slug || $caseStudy->isDirty('name')) {
                $caseStudy->slug = $caseStudy->generateUniqueSlug($caseStudy->slug ?: $caseStudy->name);
            }

            $caseStudy->client_name ??= $caseStudy->name;
            $caseStudy->hero_title ??= $caseStudy->name;
            $caseStudy->hero_title_line_1 ??= $caseStudy->hero_title;
            $caseStudy->hero_subtitle ??= $caseStudy->client_name;
            $caseStudy->accent_color ??= '#0A7949';
            $caseStudy->results_heading ??= 'Results';
        });
    }

    protected function casts(): array
    {
        return [
            'style_variant' => 'integer',
            'is_featured' => 'boolean',
            'autoplay_video' => 'boolean',
            'touchpoints' => 'array',
            'results_stats' => 'array',
            'gallery_items' => 'array',
            'pre_callout_gallery_items' => 'array',
            'story_images' => 'array',
            'status' => CaseStatus::class,
        ];
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true)
            ->where('status', CaseStatus::Published)
            ->orderBy('sort_order');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', CaseStatus::Published);
    }

    public function isConcept(): bool
    {
        return $this->status === CaseStatus::Concept;
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
