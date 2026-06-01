<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        if (Auth::check()) {
            ActivityLogger::logCreate('Order', $order->id, $order->toArray());
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if (Auth::check()) {
            ActivityLogger::logUpdate('Order', $order->id, $order->getOriginal(), $order->getChanges());
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('Order', $order->id, $order->toArray());
        }
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        if (Auth::check()) {
            ActivityLogger::log('restore', 'Order', $order->id, 'Khôi phục Order');
        }
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('Order', $order->id, $order->toArray());
        }
    }
}
