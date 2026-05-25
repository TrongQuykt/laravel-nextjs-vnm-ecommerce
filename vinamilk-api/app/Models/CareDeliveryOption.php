<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareDeliveryOption extends Model
{
    protected $guarded = [];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'is_active'        => 'boolean',
    ];
}
