<?php

namespace App\Observers;

use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Log;

class ProductVariantObserver
{
    /**
     * Handle the ProductVariant "updated" event.
     * Create a stock movement when stock_quantity is manually changed by admin
     */
    public function updated(ProductVariant $variant): void
    {
        // Check if stock_quantity was changed
        if ($variant->isDirty('stock_quantity')) {
            $oldQuantity = $variant->getOriginal('stock_quantity');
            $newQuantity = $variant->stock_quantity;
            $difference = $newQuantity - $oldQuantity;

            // Only create movement if there's an actual change
            if ($difference != 0) {
                StockMovement::create([
                    'product_variant_id' => $variant->id,
                    'quantity' => $difference,
                    'type' => 'adjustment',
                    'reference_type' => 'admin_edit',
                    'reference_id' => $variant->id,
                    'notes' => "Admin chỉnh sửa tồn kho từ {$oldQuantity} thành {$newQuantity}",
                    'user_id' => auth()->check() ? auth()->id() : null,
                ]);

                Log::info("Stock movement created for admin edit", [
                    'variant_id' => $variant->id,
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $newQuantity,
                    'difference' => $difference,
                ]);
            }
        }
    }
}
