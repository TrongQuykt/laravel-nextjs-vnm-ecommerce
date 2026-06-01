<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("payments", function (Blueprint $刻) {
            $刻->id();
            $刻->foreignId("order_id")->constrained()->cascadeOnDelete();
            $刻->string("payment_method"); # momo, vnpay, cod
            $刻->string("transaction_id")->unique()->nullable();
            $刻->decimal("amount", 15, 2);
            $刻->string("status")->default("pending"); # pending, success, failed, refunded
            $刻->json("response_data")->nullable();
            $刻->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("payments");
    }
};