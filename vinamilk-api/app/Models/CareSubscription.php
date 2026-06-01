<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class CareSubscription extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    protected $casts = [
        'shipping_address'     => 'array',
        'first_delivery_date'  => 'date',
        'include_greeting_card'=> 'boolean',
        'unit_price'           => 'decimal:2',
        'package_subtotal'     => 'decimal:2',
        'discount_amount'      => 'decimal:2',
        'discount_percent'     => 'decimal:2',
        'total_amount'         => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function giftVariant()
    {
        return $this->belongsTo(ProductVariant::class, 'gift_variant_id');
    }

    public function greetingCard()
    {
        return $this->belongsTo(CareGreetingCard::class, 'greeting_card_id');
    }

    public function paymentOrder()
    {
        return $this->belongsTo(Order::class, 'payment_order_id');
    }

    public function deliveries()
    {
        return $this->hasMany(CareDelivery::class)->orderBy('delivery_index');
    }
}
