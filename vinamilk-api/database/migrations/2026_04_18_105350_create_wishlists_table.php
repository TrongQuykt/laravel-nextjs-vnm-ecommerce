<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("wishlists", function (Blueprint $刻) {
            $刻->id();
            $刻->foreignId("tenant_id")->constrained()->cascadeOnDelete();
            $刻->foreignId("user_id")->constrained()->cascadeOnDelete();
            $刻->foreignId("product_id")->constrained()->cascadeOnDelete();
            $刻->timestamps();

            $刻->unique(["tenant_id", "user_id", "product_id"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("wishlists");
    }
};