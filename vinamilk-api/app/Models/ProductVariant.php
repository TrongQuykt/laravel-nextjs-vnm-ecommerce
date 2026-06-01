<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = [
        "product_id", "flavor_id", "volume_id", "packaging_type_id", 
        "sku", "name", "price", "base_price", "discount_percentage", 
        "compare_at_price", "main_image", "images", "stock_quantity", 
        "reserved_quantity", "available_quantity", "last_stock_update",
        "low_stock_threshold", "out_of_stock_threshold",
        "weight_grams", "dimensions", "is_active", "position", "units_per_case", "units_per_pack"
    ];

    protected $casts = [
        "dimensions" => "json",
        "images" => "json",
        "price" => "decimal:2", 
        "base_price" => "decimal:2",
        "compare_at_price" => "decimal:2",
        "discount_percentage" => "integer",
        "stock_quantity" => "integer",
        "reserved_quantity" => "integer",
        "available_quantity" => "integer",
        "low_stock_threshold" => "integer",
        "out_of_stock_threshold" => "integer",
        "last_stock_update" => "datetime",
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
    
    /**
     * Stock reservations for this variant
     */
    public function stockReservations()
    {
        return $this->hasMany(StockReservation::class);
    }
    
    /**
     * Stock movements for this variant
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Stock alerts for this variant
     */
    public function stockAlerts()
    {
        return $this->hasMany(StockAlert::class);
    }

    /**
     * Check if variant is in stock
     */
    public function isInStock(): bool
    {
        return $this->available_quantity > 0;
    }

    /**
     * Check if variant has low stock
     */
    public function isLowStock(): bool
    {
        return $this->available_quantity <= $this->low_stock_threshold && $this->available_quantity > 0;
    }

    /**
     * Check if variant is out of stock
     */
    public function isOutOfStock(): bool
    {
        return $this->available_quantity <= 0;
    }

    /**
     * Get stock status label
     */
    public function getStockStatusLabelAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'Hết hàng';
        }

        if ($this->isLowStock()) {
            return 'Sắp hết hàng';
        }

        return 'Còn hàng';
    }
}