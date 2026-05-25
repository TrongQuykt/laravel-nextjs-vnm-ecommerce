<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        "tenant_id", 
        "user_id", 
        "order_number",
        "order_type",
        "care_subscription_id",
        "status", 
        "delivery_type",
        "store_id",
        "pickup_time",
        "invoice_info",
        "shipping_method_id",
        "shipping_method_name",
        "expected_delivery_date",
        "total_amount", 
        "discount_amount", 
        "shipping_cost", 
        "payment_status", 
        "payment_method", 
        "notes", 
        "shipping_address",
        "tracking_number",
        "voucher_code"
    ];

    protected $casts = [
        "shipping_address" => "json", 
        "invoice_info" => "json",
        "total_amount" => "decimal:2",
        "expected_delivery_date" => "date"
    ];

    protected static function booted()
    {
        static::updating(function ($order) {
            // Check if status has changed to 'shipping'
            if ($order->isDirty('status') && $order->status === 'shipping') {
                if ($order->delivery_type === 'shipping' && empty($order->tracking_number)) {
                    $ghn = new \App\Services\GhnService();
                    $trackingNumber = $ghn->createShippingOrder($order);
                    if ($trackingNumber) {
                        $order->tracking_number = $trackingNumber;
                        $order->notes = ($order->notes ? $order->notes . "\n" : "") . "Đã kết nối GHN Sandbox. Mã vận đơn: " . $trackingNumber;
                    }
                }
            }
        });
    }

    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(OrderItem::class); }
    public function payment() { return $this->hasOne(Payment::class); }
    public function store() { return $this->belongsTo(Store::class); }
    public function shippingMethod() { return $this->belongsTo(ShippingMethod::class); }
    public function careSubscription() { return $this->belongsTo(CareSubscription::class); }
}