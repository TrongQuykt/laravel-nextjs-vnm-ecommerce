<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions_page_banners', function (Blueprint $table) {
            $table->foreignId('promotion_banner_id')
                ->nullable()
                ->after('id')
                ->constrained('promotion_banners')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('promotions_page_banners', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promotion_banner_id');
        });
    }
};
