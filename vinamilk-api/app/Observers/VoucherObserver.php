<?php

namespace App\Observers;

use App\Models\Coupon;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class VoucherObserver
{
    /**
     * Handle the Coupon "created" event.
     */
    public function created(Coupon $coupon): void
    {
        if (Auth::check()) {
            ActivityLogger::logCreate('Voucher', $coupon->id, $coupon->toArray());
        }
    }

    /**
     * Handle the Coupon "updated" event.
     */
    public function updated(Coupon $coupon): void
    {
        if (Auth::check()) {
            ActivityLogger::logUpdate('Voucher', $coupon->id, $coupon->getOriginal(), $coupon->getChanges());
        }
    }

    /**
     * Handle the Coupon "deleted" event.
     */
    public function deleted(Coupon $coupon): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('Voucher', $coupon->id, $coupon->toArray());
        }
    }

    /**
     * Handle the Coupon "restored" event.
     */
    public function restored(Coupon $coupon): void
    {
        if (Auth::check()) {
            ActivityLogger::log('restore', 'Voucher', $coupon->id, 'Khôi phục Voucher');
        }
    }

    /**
     * Handle the Coupon "force deleted" event.
     */
    public function forceDeleted(Coupon $coupon): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('Voucher', $coupon->id, $coupon->toArray());
        }
    }
}
