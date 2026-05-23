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
        Schema::create('coupons', function (Blueprint $刻) {
            $刻->id();
            $刻->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $刻->string('code')->unique();
            $刻->string('type'); # percentage, fixed
            $刻->decimal('value', 15, 2);
            $刻->decimal('min_order_value', 15, 2)->default(0);
            $刻->integer('usage_limit')->nullable();
            $刻->integer('used_count')->default(0);
            $刻->timestamp('start_at')->nullable();
            $刻->timestamp('end_at')->nullable();
            $刻->boolean('is_active')->default(true);
            $刻->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
