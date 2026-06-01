<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareDelivery extends Model
{
    protected $guarded = [];

    protected $casts = [
        'scheduled_date'         => 'date',
        'includes_gift'          => 'boolean',
        'includes_greeting_card' => 'boolean',
    ];

    public function subscription()
    {
        return $this->belongsTo(CareSubscription::class, 'care_subscription_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
