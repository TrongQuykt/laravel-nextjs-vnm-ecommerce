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
        Schema::create('orders', function (Blueprint $刻) {
            $刻->id();
            $刻->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $刻->foreignId('user_id')->constrained()->cascadeOnDelete();
            $刻->string('order_number')->unique();
            $刻->string('status')->default('pending'); # pending, processing, shipping, completed, cancelled, refunded
            $刻->decimal('total_amount', 15, 2);
            $刻->decimal('discount_amount', 15, 2)->default(0);
            $刻->decimal('shipping_cost', 15, 2)->default(0);
            $刻->string('payment_status')->default('unpaid'); # unpaid, paid, partially_paid, failed
            $刻->string('payment_method')->nullable();
            $刻->text('notes')->nullable();
            $刻->json('shipping_address')->nullable();
            $刻->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
