<?php

namespace App\Models;

use App\Enums\CaseStatus;
use App\Models\Concerns\HasUniqueSlug;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'slug',
    'category',
    'tags',
    'author_name',
    'author_photo',
    'header_photo',
    'intro',
    'content',
    'status',
    'published_at',
    'seo_title',
    'meta_description',
])]
class Blog extends Model
{
    use HasUniqueSlug;
    use LogsActivity;

    protected function casts(): array
    {
        return [
            'status' => CaseStatus::class,
            'published_at' => 'datetime',
            'tags' => 'array',
        ];
    }

    protected function slugSource(): string
    {
        return 'title';
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
}
