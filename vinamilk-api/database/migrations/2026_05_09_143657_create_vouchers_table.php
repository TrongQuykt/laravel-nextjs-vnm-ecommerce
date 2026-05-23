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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('banner_image')->nullable();
            $table->enum('type', ['percent', 'fixed'])->default('percent');
            $table->decimal('discount_value', 10, 2);              // % hoặc số tiền
            $table->decimal('max_discount_amount', 12, 2)->nullable(); // Giảm tối đa (dùng cho type=percent)
            $table->decimal('min_order_amount', 12, 2)->default(0); // Đơn tối thiểu
            $table->json('applicable_product_ids')->nullable();     // null = tất cả sản phẩm
            $table->unsignedInteger('total_quantity')->default(0);  // 0 = không giới hạn
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
