<?php

namespace App\Observers;

use App\Models\Category;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class CategoryObserver
{
    /**
     * Handle the Category "created" event.
     */
    public function created(Category $category): void
    {
        if (Auth::check()) {
            ActivityLogger::logCreate('Category', $category->id, $category->toArray());
        }
    }

    /**
     * Handle the Category "updated" event.
     */
    public function updated(Category $category): void
    {
        if (Auth::check()) {
            ActivityLogger::logUpdate('Category', $category->id, $category->getOriginal(), $category->getChanges());
        }
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('Category', $category->id, $category->toArray());
        }
    }

    /**
     * Handle the Category "restored" event.
     */
    public function restored(Category $category): void
    {
        if (Auth::check()) {
            ActivityLogger::log('restore', 'Category', $category->id, 'Khôi phục Category');
        }
    }

    /**
     * Handle the Category "force deleted" event.
     */
    public function forceDeleted(Category $category): void
    {
        if (Auth::check()) {
            ActivityLogger::logDelete('Category', $category->id, $category->toArray());
        }
    }
}
