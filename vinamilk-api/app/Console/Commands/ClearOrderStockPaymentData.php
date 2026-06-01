<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearOrderStockPaymentData extends Command
{
    protected $signature = 'data:clear-order-stock-payment';
    protected $description = 'Xóa dữ liệu từ các bảng order, stock, payment';

    public function handle()
    {
        $this->warn('CẢNH BÁO: Hành động này sẽ xóa TẤT CẢ dữ liệu từ các bảng sau:');
        $this->warn('- orders');
        $this->warn('- order_items');
        $this->warn('- payments');
        $this->warn('- stock_movements');
        $this->warn('- stock_reservations');
        $this->warn('- stock_alerts');
        $this->warn('- activity_logs');
        $this->line('');

        if (!$this->confirm('Bạn có chắc chắn muốn tiếp tục?')) {
            $this->info('Đã hủy.');
            return;
        }

        $this->info('Đang xóa dữ liệu...');

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            DB::table('order_items')->truncate();
            $this->info('✓ Đã xóa order_items');

            DB::table('orders')->truncate();
            $this->info('✓ Đã xóa orders');

            DB::table('payments')->truncate();
            $this->info('✓ Đã xóa payments');

            DB::table('stock_movements')->truncate();
            $this->info('✓ Đã xóa stock_movements');

            DB::table('stock_reservations')->truncate();
            $this->info('✓ Đã xóa stock_reservations');

            DB::table('stock_alerts')->truncate();
            $this->info('✓ Đã xóa stock_alerts');

            // Chỉ xóa activity_logs liên quan đến order, stock, payment
            $activityLogCount = DB::table('activity_logs')
                ->whereIn('resource_type', [
                    'App\Models\Order',
                    'App\Models\OrderItem',
                    'App\Models\Payment',
                    'App\Models\StockMovement',
                    'App\Models\StockReservation',
                    'App\Models\StockAlert',
                ])
                ->delete();
            $this->info("✓ Đã xóa {$activityLogCount} activity_logs liên quan đến order/stock/payment");

            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $this->line('');
            $this->info('✅ Đã xóa thành công tất cả dữ liệu!');
        } catch (\Exception $e) {
            $this->error('Lỗi: ' . $e->getMessage());
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
}
