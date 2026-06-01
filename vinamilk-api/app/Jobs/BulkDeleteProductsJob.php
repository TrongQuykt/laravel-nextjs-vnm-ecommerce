<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\ActivityLogger;

class BulkDeleteProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $productIds;
    protected $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(array $productIds, $userId)
    {
        $this->productIds = $productIds;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Product::whereIn('id', $this->productIds)->get()->each(function ($product) {
                $product->delete();
                
                // Log activity
                ActivityLogger::logDelete('Product', $product->id);
            });

            Log::info('Bulk delete products completed', [
                'count' => count($this->productIds),
                'user_id' => $this->userId,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to bulk delete products', [
                'error' => $e->getMessage(),
                'product_ids' => $this->productIds,
            ]);
            throw $e;
        }
    }
}
