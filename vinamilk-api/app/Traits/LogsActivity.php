<?php

namespace App\Traits;

use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            if (Auth::check()) {
                ActivityLogger::logCreate(
                    class_basename($model),
                    $model->id,
                    $model->toArray()
                );
            }
        });

        static::updated(function ($model) {
            if (Auth::check()) {
                ActivityLogger::logUpdate(
                    class_basename($model),
                    $model->id,
                    $model->getOriginal(),
                    $model->getChanges()
                );
            }
        });

        static::deleted(function ($model) {
            if (Auth::check()) {
                ActivityLogger::logDelete(
                    class_basename($model),
                    $model->id,
                    $model->toArray()
                );
            }
        });
    }
}
