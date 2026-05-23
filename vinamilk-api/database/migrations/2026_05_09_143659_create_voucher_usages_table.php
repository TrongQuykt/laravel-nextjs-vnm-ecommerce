<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->timestamp('used_at')->useCurrent();
            $table->timestamps();

            $table->unique(['voucher_id', 'user_id']); // 1 user chỉ dùng 1 voucher 1 lần
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_usages');
    }
};
