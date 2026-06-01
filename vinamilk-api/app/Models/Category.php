<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use BelongsToTenant;

    protected $fillable = ["tenant_id", "brand_id", "parent_id", "name", "slug", "description", "image", "is_active", "loyalty_rate"];

    public function brand() { return $this->belongsTo(Brand::class); }
    public function parent() { return $this->belongsTo(Category::class, "parent_id"); }
    public function children() { return $this->hasMany(Category::class, "parent_id"); }
    public function products() { return $this->hasMany(Product::class); }
    public function productLines() { return $this->hasMany(ProductLine::class); }
}