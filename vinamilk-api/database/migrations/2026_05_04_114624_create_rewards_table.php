<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('voucher'); // voucher, gift
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('points_required');
            $table->integer('stock_quantity')->default(0);
            $table->integer('user_limit')->default(1); // Giới hạn số lần mỗi user được đổi
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};
