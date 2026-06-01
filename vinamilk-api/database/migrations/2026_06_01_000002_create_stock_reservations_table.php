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
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->index();
            $table->integer('quantity');
            $table->timestamp('reserved_at');
            $table->timestamp('expires_at')->index();
            $table->enum('status', ['pending', 'confirmed', 'released', 'expired'])->default('pending')->index();
            $table->timestamps();
            
            // Composite indexes
            $table->index(['product_variant_id', 'status']);
            $table->index(['expires_at', 'status']);
            $table->index(['order_number', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');
    }
};
