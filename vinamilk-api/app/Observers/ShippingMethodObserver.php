<?php

namespace App\Observers;

use App\Models\ShippingMethod;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class ShippingMethodObserver
{
    /**
     * Handle the ShippingMethod "created" event.
     */
    public function created(ShippingMethod $shippingMethod): void
    {
        if (Auth::check()) {
            ActivityLogger::logCreate('ShippingMethod', $shippingMethod->id, $shippingMethod->toArray());
        }
    }

    /**
     * Handle the ShippingMethod "updated" event.
     */
    public function updated(ShippingMethod $shippingMethod): void
    {
        if (Auth::check()) {
            ActivityLogger::logUpdate('ShippingMethod', $shippingMethod->id, $shippingMethod->getOriginal(), $shippingMethod->getChanges());
        }
    }

    /**
     * Handle the ShippingMethod "deleted" event.
     */
    public function deleted(ShippingMethod $shippingMethod): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('ShippingMethod', $shippingMethod->id, $shippingMethod->toArray());
        }
    }

    /**
     * Handle the ShippingMethod "restored" event.
     */
    public function restored(ShippingMethod $shippingMethod): void
    {
        if (Auth::check()) {
            ActivityLogger::log('restore', 'ShippingMethod', $shippingMethod->id, 'Khôi phục ShippingMethod');
        }
    }

    /**
     * Handle the ShippingMethod "force deleted" event.
     */
    public function forceDeleted(ShippingMethod $shippingMethod): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('ShippingMethod', $shippingMethod->id, $shippingMethod->toArray());
        }
    }
}
