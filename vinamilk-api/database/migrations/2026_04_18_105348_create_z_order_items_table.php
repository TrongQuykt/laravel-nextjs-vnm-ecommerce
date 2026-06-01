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
        Schema::create('order_items', function (Blueprint $刻) {
            $刻->id();
            $刻->foreignId('order_id')->constrained()->cascadeOnDelete();
            $刻->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $刻->string('product_name'); # Snapshot at time of purchase
            $刻->string('variant_name')->nullable();
            $刻->integer('quantity');
            $刻->decimal('price', 15, 2);
            $刻->decimal('total', 15, 2);
            $刻->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
