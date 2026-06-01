<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\StockReservation;
use App\Models\StockMovement;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockService
{
    /**
     * Reserve stock for an order using Pessimistic Locking
     * 
     * @param int $productVariantId
     * @param int $quantity
     * @param string $orderNumber
     * @param int $timeoutMinutes Timeout in minutes (default: 15)
     * @return bool
     * @throws \Exception
     */
    public function reserveStock(int $productVariantId, int $quantity, string $orderNumber, int $timeoutMinutes = 15): bool
    {
        return DB::transaction(function () use ($productVariantId, $quantity, $orderNumber, $timeoutMinutes) {
            // Lock the variant row to prevent concurrent modifications
            $variant = ProductVariant::where('id', $productVariantId)
                ->lockForUpdate()
                ->first();

            if (!$variant) {
                throw new \Exception('Sản phẩm không tồn tại');
            }

            // Calculate available stock (stock_quantity - reserved_quantity)
            $availableStock = $variant->stock_quantity - $variant->reserved_quantity;
            
            if ($availableStock < $quantity) {
                throw new \Exception("Không đủ hàng trong kho. Còn lại: {$availableStock}, Yêu cầu: {$quantity}");
            }

            // Increment reserved_quantity
            $variant->increment('reserved_quantity', $quantity);
            $variant->update(['last_stock_update' => now()]);

            // Create stock reservation record
            StockReservation::create([
                'product_variant_id' => $productVariantId,
                'order_number' => $orderNumber,
                'quantity' => $quantity,
                'reserved_at' => now(),
                'expires_at' => now()->addMinutes($timeoutMinutes),
                'status' => 'pending',
            ]);

            // Log stock movement
            StockMovement::create([
                'product_variant_id' => $productVariantId,
                'quantity' => -$quantity,
                'type' => 'reservation',
                'reference_type' => 'order',
                'reference_id' => $orderNumber,
                'user_id' => auth()->id(),
            ]);

            Log::info("Stock reserved", [
                'variant_id' => $productVariantId,
                'order_number' => $orderNumber,
                'quantity' => $quantity,
                'available_before' => $availableStock,
                'available_after' => $availableStock - $quantity,
            ]);

            return true;
        });
    }

    /**
     * Confirm stock reservation when payment is successful
     * 
     * @param string $orderNumber
     * @return void
     */
    public function confirmStock(string $orderNumber): void
    {
        DB::transaction(function () use ($orderNumber) {
            // Get all reservations for this order
            $reservations = StockReservation::where('order_number', $orderNumber)
                ->where('status', 'pending')
                ->get();

            foreach ($reservations as $reservation) {
                // Lock the variant row
                $variant = ProductVariant::where('id', $reservation->product_variant_id)
                    ->lockForUpdate()
                    ->first();

                if (!$variant) {
                    Log::error("Variant not found for reservation", [
                        'reservation_id' => $reservation->id,
                        'variant_id' => $reservation->product_variant_id,
                    ]);
                    continue;
                }

                // Decrement stock_quantity (actual stock)
                $variant->decrement('stock_quantity', $reservation->quantity);
                
                // Decrement reserved_quantity (release the reservation)
                $variant->decrement('reserved_quantity', $reservation->quantity);
                $variant->update(['last_stock_update' => now()]);

                // Update reservation status
                $reservation->update(['status' => 'confirmed']);

                // Log stock movement
                StockMovement::create([
                    'product_variant_id' => $reservation->product_variant_id,
                    'quantity' => -$reservation->quantity,
                    'type' => 'export',
                    'reference_type' => 'order',
                    'reference_id' => $orderNumber,
                    'user_id' => auth()->id(),
                ]);

                Log::info("Stock confirmed", [
                    'variant_id' => $reservation->product_variant_id,
                    'order_number' => $orderNumber,
                    'quantity' => $reservation->quantity,
                ]);
            }
        });
    }

    /**
     * Release stock reservation when order is cancelled or expired
     * 
     * @param string $orderNumber
     * @return void
     */
    public function releaseStock(string $orderNumber): void
    {
        DB::transaction(function () use ($orderNumber) {
            // Get all pending reservations for this order
            $reservations = StockReservation::where('order_number', $orderNumber)
                ->where('status', 'pending')
                ->get();

            foreach ($reservations as $reservation) {
                // Lock the variant row
                $variant = ProductVariant::where('id', $reservation->product_variant_id)
                    ->lockForUpdate()
                    ->first();

                if (!$variant) {
                    Log::error("Variant not found for reservation", [
                        'reservation_id' => $reservation->id,
                        'variant_id' => $reservation->product_variant_id,
                    ]);
                    continue;
                }

                // Decrement reserved_quantity (release the reservation)
                $variant->decrement('reserved_quantity', $reservation->quantity);
                $variant->update(['last_stock_update' => now()]);

                // Update reservation status
                $reservation->update(['status' => 'released']);

                // Log stock movement
                StockMovement::create([
                    'product_variant_id' => $reservation->product_variant_id,
                    'quantity' => $reservation->quantity,
                    'type' => 'release',
                    'reference_type' => 'order',
                    'reference_id' => $orderNumber,
                    'user_id' => auth()->id(),
                ]);

                Log::info("Stock released", [
                    'variant_id' => $reservation->product_variant_id,
                    'order_number' => $orderNumber,
                    'quantity' => $reservation->quantity,
                ]);
            }
        });
    }

    /**
     * Release expired reservations (called by cron job)
     * 
     * @return int Number of reservations released
     */
    public function releaseExpiredReservations(): int
    {
        $expiredReservations = StockReservation::where('expires_at', '<', now())
            ->where('status', 'pending')
            ->get();

        $count = 0;

        foreach ($expiredReservations as $reservation) {
            try {
                $this->releaseStock($reservation->order_number);
                $reservation->update(['status' => 'expired']);
                $count++;
            } catch (\Exception $e) {
                Log::error("Failed to release expired reservation", [
                    'reservation_id' => $reservation->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    /**
     * Add stock (import)
     * 
     * @param int $productVariantId
     * @param int $quantity
     * @param string $referenceType
     * @param string $referenceId
     * @param int|null $warehouseId
     * @param string|null $notes
     * @return void
     */
    public function addStock(int $productVariantId, int $quantity, string $referenceType, string $referenceId, ?int $warehouseId = null, ?string $notes = null): void
    {
        DB::transaction(function () use ($productVariantId, $quantity, $referenceType, $referenceId, $warehouseId, $notes) {
            // Lock the variant row
            $variant = ProductVariant::where('id', $productVariantId)
                ->lockForUpdate()
                ->first();

            if (!$variant) {
                throw new \Exception('Sản phẩm không tồn tại');
            }

            // Increment stock_quantity
            $variant->increment('stock_quantity', $quantity);
            $variant->update(['last_stock_update' => now()]);

            // Log stock movement
            StockMovement::create([
                'product_variant_id' => $productVariantId,
                'quantity' => $quantity,
                'type' => 'import',
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'warehouse_id' => $warehouseId,
                'user_id' => auth()->id(),
                'notes' => $notes,
            ]);

            Log::info("Stock added", [
                'variant_id' => $productVariantId,
                'quantity' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);
        });
    }

    /**
     * Adjust stock (manual adjustment)
     * 
     * @param int $productVariantId
     * @param int $newQuantity
     * @param string $reason
     * @param string|null $notes
     * @return void
     */
    public function adjustStock(int $productVariantId, int $newQuantity, string $reason, ?string $notes = null): void
    {
        DB::transaction(function () use ($productVariantId, $newQuantity, $reason, $notes) {
            // Lock the variant row
            $variant = ProductVariant::where('id', $productVariantId)
                ->lockForUpdate()
                ->first();

            if (!$variant) {
                throw new \Exception('Sản phẩm không tồn tại');
            }

            $oldQuantity = $variant->stock_quantity;
            $difference = $newQuantity - $oldQuantity;

            if ($difference === 0) {
                return; // No change needed
            }

            // Update stock_quantity
            $variant->update([
                'stock_quantity' => $newQuantity,
                'last_stock_update' => now(),
            ]);

            // Log stock movement
            StockMovement::create([
                'product_variant_id' => $productVariantId,
                'quantity' => $difference,
                'type' => 'adjustment',
                'reference_type' => 'adjustment',
                'reference_id' => $reason,
                'user_id' => auth()->id(),
                'notes' => $notes,
            ]);

            Log::info("Stock adjusted", [
                'variant_id' => $productVariantId,
                'old_quantity' => $oldQuantity,
                'new_quantity' => $newQuantity,
                'difference' => $difference,
                'reason' => $reason,
            ]);
        });
    }

    /**
     * Get available stock for a variant
     * 
     * @param int $productVariantId
     * @return int
     */
    public function getAvailableStock(int $productVariantId): int
    {
        $variant = ProductVariant::find($productVariantId);
        
        if (!$variant) {
            return 0;
        }

        return $variant->available_quantity;
    }

    /**
     * Check if stock is available
     * 
     * @param int $productVariantId
     * @param int $quantity
     * @return bool
     */
    public function isStockAvailable(int $productVariantId, int $quantity): bool
    {
        return $this->getAvailableStock($productVariantId) >= $quantity;
    }

    /**
     * Get stock movement history
     * 
     * @param int $productVariantId
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getMovementHistory(int $productVariantId, array $filters = [])
    {
        $query = StockMovement::with(['user', 'warehouse'])
            ->where('product_variant_id', $productVariantId);
        
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        if (isset($filters['from_date'])) {
            $query->where('created_at', '>=', $filters['from_date']);
        }
        
        if (isset($filters['to_date'])) {
            $query->where('created_at', '<=', $filters['to_date']);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate(50);
    }
}
