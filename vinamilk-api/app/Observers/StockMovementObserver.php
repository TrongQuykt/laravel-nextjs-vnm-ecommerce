<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\StockMovement;

class StockMovementObserver
{
    public function created(StockMovement $movement): void
    {
        ActivityLog::create([
            'user_id' => auth()->check() ? auth()->id() : null,
            'action' => 'create',
            'resource_type' => StockMovement::class,
            'resource_id' => $movement->id,
            'description' => match($movement->type) {
                'import' => "Nhập kho: {$movement->quantity} SP cho variant #{$movement->product_variant_id}",
                'export' => "Xuất kho: {$movement->quantity} SP cho variant #{$movement->product_variant_id}",
                'adjustment' => "Điều chỉnh tồn kho: {$movement->quantity} SP cho variant #{$movement->product_variant_id}",
                'reservation' => "Giữ hàng: {$movement->quantity} SP cho variant #{$movement->product_variant_id}",
                'confirmation' => "Xác nhận đơn: {$movement->quantity} SP cho variant #{$movement->product_variant_id}",
                'release' => "Hủy giữ hàng: {$movement->quantity} SP cho variant #{$movement->product_variant_id}",
                default => "Stock movement: {$movement->type} - {$movement->quantity} SP",
            },
            'new_values' => $movement->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ]);
    }
}
