<?php

namespace App\Observers;

use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        if (Auth::check()) {
            ActivityLogger::logCreate('Product', $product->id, $product->toArray());
        }
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        if (Auth::check()) {
            $oldValues = $product->getOriginal();
            $newValues = $product->getChanges();
            ActivityLogger::logUpdate('Product', $product->id, $oldValues, $newValues);
        }
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('Product', $product->id, $product->toArray());
        }
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        if (Auth::check()) {
            ActivityLogger::log('restore', 'Product', $product->id, 'Khôi phục Product');
        }
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('Product', $product->id, $product->toArray());
        }
    }
}
