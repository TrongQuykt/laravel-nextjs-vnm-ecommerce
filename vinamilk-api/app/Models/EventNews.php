<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventNews extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'banner_image',
        'content',
        'table_description',
        'published_at',
        'is_published',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('published_at', '<=', now());
    }
}
