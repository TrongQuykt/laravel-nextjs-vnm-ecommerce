<?php

namespace App\Observers;

use App\Models\Store;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class StoreObserver
{
    /**
     * Handle the Store "created" event.
     */
    public function created(Store $store): void
    {
        if (Auth::check()) {
            ActivityLogger::logCreate('Store', $store->id, $store->toArray());
        }
    }

    /**
     * Handle the Store "updated" event.
     */
    public function updated(Store $store): void
    {
        if (Auth::check()) {
            ActivityLogger::logUpdate('Store', $store->id, $store->getOriginal(), $store->getChanges());
        }
    }

    /**
     * Handle the Store "deleted" event.
     */
    public function deleted(Store $store): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('Store', $store->id, $store->toArray());
        }
    }

    /**
     * Handle the Store "restored" event.
     */
    public function restored(Store $store): void
    {
        if (Auth::check()) {
            ActivityLogger::log('restore', 'Store', $store->id, 'Khôi phục Store');
        }
    }

    /**
     * Handle the Store "force deleted" event.
     */
    public function forceDeleted(Store $store): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('Store', $store->id, $store->toArray());
        }
    }
}
