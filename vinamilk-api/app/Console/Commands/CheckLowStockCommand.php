<?php

namespace App\Console\Commands;

use App\Models\ProductVariant;
use App\Models\StockAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckLowStockCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-low-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for low stock and out of stock items';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking stock levels...');
        
        // Check low stock
        $lowStockVariants = ProductVariant::whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('stock_quantity', '>', 0)
            ->where('is_active', true)
            ->whereDoesntHave('stockAlerts', function ($query) {
                $query->where('type', 'low_stock')->where('is_resolved', false);
            })
            ->get();
        
        foreach ($lowStockVariants as $variant) {
            // Send alert to admin (implement notification logic)
            // event(new LowStockAlert($variant));
            
            // Log alert
            StockAlert::create([
                'product_variant_id' => $variant->id,
                'type' => 'low_stock',
                'current_quantity' => $variant->stock_quantity,
                'threshold' => $variant->low_stock_threshold,
            ]);

            $this->warn("Low stock alert: {$variant->name} - Available: {$variant->stock_quantity}");

            Log::warning("Low stock detected", [
                'variant_id' => $variant->id,
                'variant_name' => $variant->name,
                'available_quantity' => $variant->stock_quantity,
                'threshold' => $variant->low_stock_threshold,
            ]);
        }
        
        // Check out of stock
        $outOfStockVariants = ProductVariant::where('stock_quantity', '<=', 0)
            ->where('is_active', true)
            ->whereDoesntHave('stockAlerts', function ($query) {
                $query->where('type', 'out_of_stock')->where('is_resolved', false);
            })
            ->get();
        
        foreach ($outOfStockVariants as $variant) {
            // Send urgent alert to admin (implement notification logic)
            // event(new OutOfStockAlert($variant));
            
            // Log alert
            StockAlert::create([
                'product_variant_id' => $variant->id,
                'type' => 'out_of_stock',
                'current_quantity' => $variant->stock_quantity,
                'threshold' => 0,
            ]);

            $this->error("Out of stock alert: {$variant->name}");

            Log::error("Out of stock detected", [
                'variant_id' => $variant->id,
                'variant_name' => $variant->name,
            ]);
        }
        
        $totalAlerts = $lowStockVariants->count() + $outOfStockVariants->count();
        $this->info("Checked stock levels. Found {$totalAlerts} alerts.");
        
        return Command::SUCCESS;
    }
}
