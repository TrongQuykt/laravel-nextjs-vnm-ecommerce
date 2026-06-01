<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("banners", function (Blueprint $刻) {
            $刻->id();
            $刻->foreignId("tenant_id")->constrained()->cascadeOnDelete();
            $刻->string("title")->nullable();
            $刻->string("image");
            $刻->string("link")->nullable();
            $刻->string("position")->default("home_hero");
            $刻->integer("sort_order")->default(0);
            $刻->boolean("is_active")->default(true);
            $刻->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("banners");
    }
};