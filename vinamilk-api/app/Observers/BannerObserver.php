<?php

namespace App\Observers;

use App\Models\Banner;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class BannerObserver
{
    /**
     * Handle the Banner "created" event.
     */
    public function created(Banner $banner): void
    {
        if (Auth::check()) {
            ActivityLogger::logCreate('Banner', $banner->id, $banner->toArray());
        }
    }

    /**
     * Handle the Banner "updated" event.
     */
    public function updated(Banner $banner): void
    {
        if (Auth::check()) {
            ActivityLogger::logUpdate('Banner', $banner->id, $banner->getOriginal(), $banner->getChanges());
        }
    }

    /**
     * Handle the Banner "deleted" event.
     */
    public function deleted(Banner $banner): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('Banner', $banner->id, $banner->toArray());
        }
    }

    /**
     * Handle the Banner "restored" event.
     */
    public function restored(Banner $banner): void
    {
        if (Auth::check()) {
            ActivityLogger::log('restore', 'Banner', $banner->id, 'Khôi phục Banner');
        }
    }

    /**
     * Handle the Banner "force deleted" event.
     */
    public function forceDeleted(Banner $banner): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('Banner', $banner->id, $banner->toArray());
        }
    }
}
