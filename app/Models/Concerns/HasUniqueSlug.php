<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Generates a unique `slug` once, from the attribute named by slugSource().
 * A slug that already exists is never regenerated when the title changes,
 * so published URLs stay stable; an edited slug is normalised and made unique.
 *
 * @mixin Model
 */
trait HasUniqueSlug
{
    abstract protected function slugSource(): string;

    protected function slugFallback(): string
    {
        return Str::snake(class_basename($this), '-');
    }

    protected static function bootHasUniqueSlug(): void
    {
        static::saving(function (Model $model): void {
            /** @var Model&self $model */
            if (blank($model->slug)) {
                $model->slug = $model->generateUniqueSlug((string) $model->getAttribute($model->slugSource()));
            } elseif ($model->isDirty('slug')) {
                $model->slug = $model->generateUniqueSlug((string) $model->slug);
            }
        });
    }

    protected function generateUniqueSlug(string $value): string
    {
        $baseSlug = Str::slug($value) ?: $this->slugFallback();
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
