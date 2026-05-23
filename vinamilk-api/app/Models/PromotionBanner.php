<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionBanner extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active'                    => 'boolean',
        'is_shown_on_promotions_page'  => 'boolean',
        'start_date'                   => 'date',
        'end_date'                     => 'date',
        'modal_table_data'             => 'array',
        'modal_image_path'             => 'string',
    ];

    public function campaign()
    {
        return $this->belongsTo(PromotionCampaign::class, 'campaign_id');
    }
}
