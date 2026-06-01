<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_flash_sale_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_flash_sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['promotion_flash_sale_id', 'product_id'], 'pfs_product_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_flash_sale_products');
    }
};
