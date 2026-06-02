<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventNews;
use Illuminate\Http\Request;

class EventNewsController extends Controller
{
    public function index(Request $request)
    {
        $query = EventNews::published();

        return $query->orderBy('published_at', 'desc')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'slug' => $event->slug,
                    'excerpt' => $event->excerpt,
                    'banner_image' => $event->banner_image ? asset('storage/' . $event->banner_image) : null,
                    'published_at' => $event->published_at,
                ];
            });
    }

    public function show($slug)
    {
        $event = EventNews::where('slug', $slug)
            ->published()
            ->firstOrFail();

        return [
            'id' => $event->id,
            'title' => $event->title,
            'slug' => $event->slug,
            'excerpt' => $event->excerpt,
            'banner_image' => $event->banner_image ? asset('storage/' . $event->banner_image) : null,
            'content' => $event->content,
            'table_description' => $event->table_description,
            'published_at' => $event->published_at,
        ];
    }
}
