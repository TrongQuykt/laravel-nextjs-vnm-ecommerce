<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionCampaign extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'is_active'  => 'boolean',
    ];

    /** Trả về true nếu chiến dịch đang trong thời gian hoạt động */
    public function isRunning(): bool
    {
        $today = now()->startOfDay();
        return $this->is_active
            && $this->start_date <= $today
            && $this->end_date   >= $today;
    }

    /* ─── Relationships ─── */

    public function pageSetting()
    {
        return $this->hasOne(PromotionPageSetting::class, 'campaign_id');
    }

    public function banners()
    {
        return $this->hasMany(PromotionBanner::class, 'campaign_id')
                    ->where('is_active', true)
                    ->orderBy('sort_order');
    }

    public function flashSale()
    {
        return $this->hasOne(PromotionFlashSale::class, 'campaign_id');
    }

    public function terms()
    {
        return $this->hasMany(PromotionTerm::class, 'campaign_id')
                    ->orderBy('sort_order');
    }
}
