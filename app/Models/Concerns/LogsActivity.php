<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function (Model $model): void {
            ActivityLog::record($model, 'created');
        });

        static::updated(function (Model $model): void {
            ActivityLog::record($model, 'updated');
        });

        static::deleted(function (Model $model): void {
            ActivityLog::record($model, 'deleted');
        });
    }
}
