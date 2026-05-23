<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("carts", function (Blueprint $刻) {
            $刻->id();
            $刻->foreignId("tenant_id")->constrained()->cascadeOnDelete();
            $刻->foreignId("user_id")->nullable()->constrained()->cascadeOnDelete();
            $刻->string("guest_id")->nullable()->index();
            $刻->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("carts");
    }
};