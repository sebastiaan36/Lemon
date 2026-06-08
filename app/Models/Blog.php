<?php

namespace App\Models;

use App\Enums\CaseStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'title',
    'slug',
    'header_photo',
    'content',
    'status',
    'published_at',
    'seo_title',
    'meta_description',
])]
class Blog extends Model
{
    protected function casts(): array
    {
        return [
            'status' => CaseStatus::class,
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $blog): void {
            if (! $blog->slug || $blog->isDirty('title')) {
                $blog->slug = $blog->generateUniqueSlug($blog->slug ?: $blog->title);
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', CaseStatus::Published)
            ->where(function (Builder $query): void {
                $query
                    ->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    protected function generateUniqueSlug(string $value): string
    {
        $baseSlug = Str::slug($value) ?: 'blog';
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
