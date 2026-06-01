<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotion_banners', function (Blueprint $table) {
            $table->integer('col_span')->default(1);
            $table->string('button_text')->nullable();
        });

        Schema::create('promotion_page_settings', function (Blueprint $table) {
            $table->id();
            $table->string('hero_image_path')->nullable();
            $table->string('hero_link_url')->nullable();
            $table->string('hero_title')->nullable();
            $table->string('hero_subtitle')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_page_settings');
        Schema::table('promotion_banners', function (Blueprint $table) {
            $table->dropColumn(['col_span', 'button_text']);
        });
    }
};
