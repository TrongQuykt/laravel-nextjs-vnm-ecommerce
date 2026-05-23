<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'order_number',
        'payment_method',
        'amount',
        'status',
        'request_payload',
        'response_payload',
        'tenant_id',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'amount' => 'decimal:2',
    ];
}
