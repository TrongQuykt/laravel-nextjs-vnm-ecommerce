<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("cart_items", function (Blueprint $刻) {
            $刻->id();
            $刻->foreignId("cart_id")->constrained()->cascadeOnDelete();
            $刻->foreignId("product_variant_id")->constrained()->cascadeOnDelete();
            $刻->integer("quantity")->default(1);
            $刻->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("cart_items");
    }
};