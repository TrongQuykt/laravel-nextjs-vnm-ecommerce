<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        "product_id", "flavor_id", "volume_id", "packaging_type_id", 
        "sku", "name", "price", "base_price", "discount_percentage", 
        "compare_at_price", "main_image", "images", "stock_quantity", 
        "weight_grams", "dimensions", "is_active", "position", "units_per_case", "units_per_pack"
    ];

    protected $casts = [
        "dimensions" => "json",
        "images" => "json",
        "price" => "decimal:2", 
        "base_price" => "decimal:2",
        "compare_at_price" => "decimal:2",
        "discount_percentage" => "integer"
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($model) {
            // Auto-calculate price based on base_price and discount_percentage
            if ($model->base_price > 0) {
                $discount = $model->discount_percentage / 100;
                $model->price = round($model->base_price * (1 - $discount), 3);
            }
        });
    }

    public function product() { return $this->belongsTo(Product::class); }
    public function flavor() { return $this->belongsTo(Flavor::class); }
    public function volume() { return $this->belongsTo(Volume::class); }
    public function packagingType() { return $this->belongsTo(PackagingType::class); }
}