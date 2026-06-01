<?php

namespace App\Observers;

use App\Models\Brand;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class BrandObserver
{
    /**
     * Handle the Brand "created" event.
     */
    public function created(Brand $brand): void
    {
        if (Auth::check()) {
            ActivityLogger::logCreate('Brand', $brand->id, $brand->toArray());
        }
    }

    /**
     * Handle the Brand "updated" event.
     */
    public function updated(Brand $brand): void
    {
        if (Auth::check()) {
            ActivityLogger::logUpdate('Brand', $brand->id, $brand->getOriginal(), $brand->getChanges());
        }
    }

    /**
     * Handle the Brand "deleted" event.
     */
    public function deleted(Brand $brand): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('Brand', $brand->id, $brand->toArray());
        }
    }

    /**
     * Handle the Brand "restored" event.
     */
    public function restored(Brand $brand): void
    {
        if (Auth::check()) {
            ActivityLogger::log('restore', 'Brand', $brand->id, 'Khôi phục Brand');
        }
    }

    /**
     * Handle the Brand "force deleted" event.
     */
    public function forceDeleted(Brand $brand): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('Brand', $brand->id, $brand->toArray());
        }
    }
}
