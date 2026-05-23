<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ProductLine extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'brand_id', 'category_id', 'name', 'slug', 'sort_order', 'description'];

    public function brand() { return $this->belongsTo(Brand::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function products() { return $this->hasMany(Product::class); }
}
