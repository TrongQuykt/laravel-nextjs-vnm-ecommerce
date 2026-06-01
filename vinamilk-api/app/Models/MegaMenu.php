<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MegaMenu extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'featured_product_id',
        'columns',
        'bottom_links',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'columns' => 'array',
        'bottom_links' => 'array',
        'is_active' => 'boolean',
    ];

    public function featuredProduct()
    {
        return $this->belongsTo(Product::class, 'featured_product_id');
    }
}
