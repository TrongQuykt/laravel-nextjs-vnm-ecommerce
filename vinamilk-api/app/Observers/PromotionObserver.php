<?php

namespace App\Observers;

use App\Models\PromotionCampaign;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class PromotionObserver
{
    /**
     * Handle the PromotionCampaign "created" event.
     */
    public function created(PromotionCampaign $promotionCampaign): void
    {
        if (Auth::check()) {
            ActivityLogger::logCreate('Promotion', $promotionCampaign->id, $promotionCampaign->toArray());
        }
    }

    /**
     * Handle the PromotionCampaign "updated" event.
     */
    public function updated(PromotionCampaign $promotionCampaign): void
    {
        if (Auth::check()) {
            ActivityLogger::logUpdate('Promotion', $promotionCampaign->id, $promotionCampaign->getOriginal(), $promotionCampaign->getChanges());
        }
    }

    /**
     * Handle the PromotionCampaign "deleted" event.
     */
    public function deleted(PromotionCampaign $promotionCampaign): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('Promotion', $promotionCampaign->id, $promotionCampaign->toArray());
        }
    }

    /**
     * Handle the PromotionCampaign "restored" event.
     */
    public function restored(PromotionCampaign $promotionCampaign): void
    {
        if (Auth::check()) {
            ActivityLogger::log('restore', 'Promotion', $promotionCampaign->id, 'Khôi phục Promotion');
        }
    }

    /**
     * Handle the PromotionCampaign "force deleted" event.
     */
    public function forceDeleted(PromotionCampaign $promotionCampaign): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('Promotion', $promotionCampaign->id, $promotionCampaign->toArray());
        }
    }
}
