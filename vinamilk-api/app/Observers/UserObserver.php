<?php

namespace App\Observers;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        if (Auth::check()) {
            ActivityLogger::logCreate('User', $user->id, $user->toArray());
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        if (Auth::check()) {
            ActivityLogger::logUpdate('User', $user->id, $user->getOriginal(), $user->getChanges());
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('User', $user->id, $user->toArray());
        }
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        if (Auth::check()) {
            ActivityLogger::log('restore', 'User', $user->id, 'Khôi phục User');
        }
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('User', $user->id, $user->toArray());
        }
    }
}
