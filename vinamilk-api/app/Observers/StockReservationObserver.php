<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\StockReservation;

class StockReservationObserver
{
    public function created(StockReservation $reservation): void
    {
        ActivityLog::create([
            'user_id' => auth()->check() ? auth()->id() : null,
            'action' => 'create',
            'resource_type' => StockReservation::class,
            'resource_id' => $reservation->id,
            'description' => "Giữ hàng: {$reservation->quantity} SP cho variant #{$reservation->product_variant_id} - Đơn {$reservation->order_number}",
            'new_values' => $reservation->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ]);
    }

    public function updated(StockReservation $reservation): void
    {
        if ($reservation->isDirty('status')) {
            ActivityLog::create([
                'user_id' => auth()->check() ? auth()->id() : null,
                'action' => 'update',
                'resource_type' => StockReservation::class,
                'resource_id' => $reservation->id,
                'description' => match($reservation->status) {
                    'confirmed' => "Xác nhận giữ hàng: {$reservation->quantity} SP cho variant #{$reservation->product_variant_id} - Đơn {$reservation->order_number}",
                    'expired' => "Hết hạn giữ hàng: {$reservation->quantity} SP cho variant #{$reservation->product_variant_id} - Đơn {$reservation->order_number}",
                    'cancelled' => "Hủy giữ hàng: {$reservation->quantity} SP cho variant #{$reservation->product_variant_id} - Đơn {$reservation->order_number}",
                    default => "Cập nhật trạng thái giữ hàng: {$reservation->status}",
                },
                'old_values' => ['status' => $reservation->getOriginal('status')],
                'new_values' => ['status' => $reservation->status],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
            ]);
        }
    }
}
