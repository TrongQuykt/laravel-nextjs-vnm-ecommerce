<?php

namespace App\Observers;

use App\Models\ChatSetting;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class ChatSettingObserver
{
    /**
     * Handle the ChatSetting "created" event.
     */
    public function created(ChatSetting $chatSetting): void
    {
        if (Auth::check()) {
            ActivityLogger::logCreate('ChatSetting', $chatSetting->id, $chatSetting->toArray());
        }
    }

    /**
     * Handle the ChatSetting "updated" event.
     */
    public function updated(ChatSetting $chatSetting): void
    {
        if (Auth::check()) {
            ActivityLogger::logUpdate('ChatSetting', $chatSetting->id, $chatSetting->getOriginal(), $chatSetting->getChanges());
        }
    }

    /**
     * Handle the ChatSetting "deleted" event.
     */
    public function deleted(ChatSetting $chatSetting): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('ChatSetting', $chatSetting->id, $chatSetting->toArray());
        }
    }

    /**
     * Handle the ChatSetting "restored" event.
     */
    public function restored(ChatSetting $chatSetting): void
    {
        if (Auth::check()) {
            ActivityLogger::log('restore', 'ChatSetting', $chatSetting->id, 'Khôi phục ChatSetting');
        }
    }

    /**
     * Handle the ChatSetting "force deleted" event.
     */
    public function forceDeleted(ChatSetting $chatSetting): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('ChatSetting', $chatSetting->id, $chatSetting->toArray());
        }
    }
}
