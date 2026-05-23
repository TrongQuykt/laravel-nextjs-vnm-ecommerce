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
        Schema::create('product_volume_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('volume_id')->constrained()->onDelete('cascade');
            $table->string('main_image')->nullable();
            $table->json('images')->nullable();
            $table->timestamps();
            
            // Đảm bảo mỗi sản phẩm chỉ có 1 bộ media cho mỗi dung tích
            $table->unique(['product_id', 'volume_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_volume_media');
    }
};
