<?php

namespace App\Observers;

use App\Models\BlogPost;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class BlogPostObserver
{
    /**
     * Handle the BlogPost "created" event.
     */
    public function created(BlogPost $blogPost): void
    {
        if (Auth::check()) {
            ActivityLogger::logCreate('BlogPost', $blogPost->id, $blogPost->toArray());
        }
    }

    /**
     * Handle the BlogPost "updated" event.
     */
    public function updated(BlogPost $blogPost): void
    {
        if (Auth::check()) {
            ActivityLogger::logUpdate('BlogPost', $blogPost->id, $blogPost->getOriginal(), $blogPost->getChanges());
        }
    }

    /**
     * Handle the BlogPost "deleted" event.
     */
    public function deleted(BlogPost $blogPost): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('BlogPost', $blogPost->id, $blogPost->toArray());
        }
    }

    /**
     * Handle the BlogPost "restored" event.
     */
    public function restored(BlogPost $blogPost): void
    {
        if (Auth::check()) {
            ActivityLogger::log('restore', 'BlogPost', $blogPost->id, 'Khôi phục BlogPost');
        }
    }

    /**
     * Handle the BlogPost "force deleted" event.
     */
    public function forceDeleted(BlogPost $blogPost): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('BlogPost', $blogPost->id, $blogPost->toArray());
        }
    }
}
