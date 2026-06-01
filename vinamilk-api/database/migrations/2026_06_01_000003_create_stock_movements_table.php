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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity'); // Positive for import, Negative for export
            $table->enum('type', [
                'import',          // Nhập kho
                'export',          // Xuất kho
                'reservation',     // Giữ kho
                'release',         // Hoàn kho
                'adjustment',      // Điều chỉnh
                'return',         // Trả hàng
                'damage',          // Hư hỏng
                'transfer',        // Chuyển kho
            ])->index();
            $table->string('reference_type')->nullable()->index(); // order, purchase_order, adjustment, etc.
            $table->string('reference_id')->nullable()->index();
            $table->text('notes')->nullable();
            $table->foreignId('warehouse_id')->nullable(); // Will add constraint after warehouses table is created
            $table->foreignId('user_id')->nullable()->constrained();
            $table->timestamps();
            
            // Composite indexes
            $table->index(['product_variant_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
