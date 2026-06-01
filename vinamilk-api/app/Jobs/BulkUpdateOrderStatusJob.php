<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\ActivityLogger;

class BulkUpdateOrderStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderIds;
    protected $status;
    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $orderIds, string $status, $userId)
    {
        $this->orderIds = $orderIds;
        $this->status = $status;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Order::whereIn('id', $this->orderIds)->get()->each(function ($order) {
                $oldStatus = $order->status;
                $order->update(['status' => $this->status]);
                
                // Log activity
                ActivityLogger::logUpdate('Order', $order->id, [
                    'old_status' => $oldStatus,
                    'new_status' => $this->status,
                ]);
            });

            Log::info('Bulk update order status completed', [
                'count' => count($this->orderIds),
                'status' => $this->status,
                'user_id' => $this->userId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to bulk update order status', [
                'error' => $e->getMessage(),
                'order_ids' => $this->orderIds,
            ]);
            throw $e;
        }
    }
}
