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
        Schema::create('shipping_methods', function (Blueprint $刻) {
            $刻->id();
            $刻->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $刻->string('name');
            $刻->string('provider'); # e.g. GHN, GHTK, ViettelPost
            $刻->decimal('base_cost', 15, 2)->default(0);
            $刻->boolean('is_active')->default(true);
            $刻->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};
