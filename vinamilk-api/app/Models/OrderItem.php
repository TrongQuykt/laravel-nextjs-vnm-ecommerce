<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        "order_id", 
        "product_variant_id", 
        "marketing_gift_id", 
        "product_name", 
        "variant_name", 
        "image", 
        "volume", 
        "packing_type", 
        "original_price",
        "quantity", 
        "price", 
        "total", 
        "package_number"
    ];

    public function order() { return $this->belongsTo(Order::class); }
    public function variant() { return $this->belongsTo(ProductVariant::class, "product_variant_id"); }
    public function marketingGift() { return $this->belongsTo(MarketingGift::class, "marketing_gift_id"); }
}