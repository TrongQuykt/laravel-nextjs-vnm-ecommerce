<?php

namespace App\Observers;

use App\Models\Reward;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class RewardObserver
{
    /**
     * Handle the Reward "created" event.
     */
    public function created(Reward $reward): void
    {
        if (Auth::check()) {
            ActivityLogger::logCreate('Reward', $reward->id, $reward->toArray());
        }
    }

    /**
     * Handle the Reward "updated" event.
     */
    public function updated(Reward $reward): void
    {
        if (Auth::check()) {
            ActivityLogger::logUpdate('Reward', $reward->id, $reward->getOriginal(), $reward->getChanges());
        }
    }

    /**
     * Handle the Reward "deleted" event.
     */
    public function deleted(Reward $reward): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('Reward', $reward->id, $reward->toArray());
        }
    }

    /**
     * Handle the Reward "restored" event.
     */
    public function restored(Reward $reward): void
    {
        if (Auth::check()) {
            ActivityLogger::log('restore', 'Reward', $reward->id, 'Khôi phục Reward');
        }
    }

    /**
     * Handle the Reward "force deleted" event.
     */
    public function forceDeleted(Reward $reward): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('Reward', $reward->id, $reward->toArray());
        }
    }
}
