<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // Số lượng đang được giữ (reserved) cho các đơn hàng chờ thanh toán
            $table->integer('reserved_quantity')->default(0)->after('stock_quantity');
            
            // Số lượng có sẵn (virtual column sẽ được tạo sau)
            // available_quantity = stock_quantity - reserved_quantity
            
            // Thời gian cập nhật tồn kho cuối cùng
            $table->timestamp('last_stock_update')->nullable()->after('reserved_quantity');
            
            // Ngưỡng cảnh báo tồn kho thấp
            $table->integer('low_stock_threshold')->default(10)->after('last_stock_update');
            
            // Ngưỡng cảnh báo hết hàng
            $table->integer('out_of_stock_threshold')->default(0)->after('low_stock_threshold');
        });

        // Tạo virtual column cho available_quantity
        DB::statement('
            ALTER TABLE product_variants 
            ADD COLUMN available_quantity INT 
            GENERATED ALWAYS AS (stock_quantity - reserved_quantity) STORED
        ');
        
        // Tạo index cho các trường mới
        Schema::table('product_variants', function (Blueprint $table) {
            $table->index('reserved_quantity');
            $table->index('available_quantity');
            $table->index('low_stock_threshold');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            // Drop indexes if they exist
            if (Schema::hasIndex('product_variants', 'reserved_quantity')) {
                $table->dropIndex(['reserved_quantity']);
            }
            if (Schema::hasIndex('product_variants', 'low_stock_threshold')) {
                $table->dropIndex(['low_stock_threshold']);
            }
            
            // Drop columns
            $table->dropColumn([
                'reserved_quantity',
                'last_stock_update',
                'low_stock_threshold',
                'out_of_stock_threshold',
            ]);
        });
        
        // Drop virtual column separately
        DB::statement('ALTER TABLE product_variants DROP COLUMN IF EXISTS available_quantity');
    }
};
