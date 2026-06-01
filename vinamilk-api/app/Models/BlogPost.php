<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'excerpt',
        'banner_image',
        'content',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'blog_post_product');
    }

    public function suggestedPosts()
    {
        return $this->belongsToMany(BlogPost::class, 'blog_post_suggested', 'blog_post_id', 'suggested_post_id');
    }
}
