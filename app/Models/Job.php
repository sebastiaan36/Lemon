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
    use HasUniqueSlug;
    use LogsActivity;

    protected $table = 'job_listings';

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'status' => CaseStatus::class,
        ];
    }

    protected function slugSource(): string
    {
        return 'title';
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', CaseStatus::Published);
    }
}
