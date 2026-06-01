<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionFlashSale extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Các sản phẩm được Admin chọn thủ công cho Flash Sale này.
     */
    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'promotion_flash_sale_products',
            'promotion_flash_sale_id',
            'product_id'
        )->withPivot('sort_order')->orderBy('promotion_flash_sale_products.sort_order');
    }

    public function campaign()
    {
        return $this->belongsTo(PromotionCampaign::class, 'campaign_id');
    }
}
