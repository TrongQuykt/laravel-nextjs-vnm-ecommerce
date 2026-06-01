<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionTerm extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'table_data' => 'array',
    ];

    public function campaign()
    {
        return $this->belongsTo(PromotionCampaign::class, 'campaign_id');
    }
}
