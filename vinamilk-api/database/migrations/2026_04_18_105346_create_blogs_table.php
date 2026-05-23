<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("blogs", function (Blueprint $刻) {
            $刻->id();
            $刻->foreignId("tenant_id")->constrained()->cascadeOnDelete();
            $刻->string("title");
            $刻->string("slug");
            $刻->longText("content")->nullable();
            $刻->string("thumbnail")->nullable();
            $刻->string("status")->default("draft");
            $刻->timestamps();

            $刻->unique(["tenant_id", "slug"]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("blogs");
    }
};