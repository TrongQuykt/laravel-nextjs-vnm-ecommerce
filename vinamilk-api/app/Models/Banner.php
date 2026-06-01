<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        "tenant_id", "product_id", "title", "subtitle", "image", "link", 
        "position", "sort_order", "is_active", "show_text", "box_text", "box_subtitle"
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}