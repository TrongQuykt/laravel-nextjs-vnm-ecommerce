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
        Schema::create('product_variants', function (Blueprint $刻) {
            $刻->id();
            $刻->foreignId('product_id')->constrained()->cascadeOnDelete();
            $刻->string('sku')->unique();
            $刻->string('name')->nullable(); # e.g. "Hộp 110ml"
            $刻->decimal('price', 15, 2);
            $刻->decimal('compare_at_price', 15, 2)->nullable();
            $刻->integer('stock_quantity')->default(0);
            $刻->integer('weight_grams')->nullable();
            $刻->json('dimensions')->nullable();
            $刻->boolean('is_active')->default(true);
            $刻->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
