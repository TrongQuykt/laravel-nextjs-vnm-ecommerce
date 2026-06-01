<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionsPageBanner extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active'        => 'boolean',
        'modal_table_data' => 'array',
    ];

    public function promotionBanner()
    {
        return $this->belongsTo(PromotionBanner::class, 'promotion_banner_id');
    }
}
