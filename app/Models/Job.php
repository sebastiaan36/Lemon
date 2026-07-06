<?php

namespace App\Models;

use App\Enums\CaseStatus;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable([
    'title',
    'job_title',
    'slug',
    'short_description',
    'body',
    'tags',
    'apply_button_text',
    'apply_email',
    'apply_contact_name',
    'apply_contact_photo',
    'status',
    'sort_order',
])]
class Job extends Model
{
    use LogsActivity;

    protected $table = 'job_listings';

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'status' => CaseStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $job): void {
            if (! $job->slug || $job->isDirty('title')) {
                $base = Str::slug($job->title);
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->where('id', '!=', $job->id ?? 0)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $job->slug = $slug;
            }
        });
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', CaseStatus::Published);
    }
}
