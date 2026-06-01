<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\StockAlert;

class StockAlertObserver
{
    public function created(StockAlert $alert): void
    {
        ActivityLog::create([
            'user_id' => auth()->check() ? auth()->id() : null,
            'action' => 'create',
            'resource_type' => StockAlert::class,
            'resource_id' => $alert->id,
            'description' => match($alert->type) {
                'low_stock' => "Cảnh báo tồn kho thấp: Variant #{$alert->product_variant_id} - Còn {$alert->current_quantity} SP (ngưỡng: {$alert->threshold})",
                'out_of_stock' => "Cảnh báo hết hàng: Variant #{$alert->product_variant_id} - Hết hàng",
                default => "Stock alert: {$alert->type}",
            },
            'new_values' => $alert->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
        ]);
    }

    public function updated(StockAlert $alert): void
    {
        if ($alert->isDirty('is_resolved') && $alert->is_resolved) {
            ActivityLog::create([
                'user_id' => auth()->check() ? auth()->id() : null,
                'action' => 'update',
                'resource_type' => StockAlert::class,
                'resource_id' => $alert->id,
                'description' => "Đánh dấu đã giải quyết cảnh báo: Variant #{$alert->product_variant_id}",
                'old_values' => ['is_resolved' => false],
                'new_values' => ['is_resolved' => true, 'resolved_at' => $alert->resolved_at],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'url' => request()->fullUrl(),
            ]);
        }
    }
}
