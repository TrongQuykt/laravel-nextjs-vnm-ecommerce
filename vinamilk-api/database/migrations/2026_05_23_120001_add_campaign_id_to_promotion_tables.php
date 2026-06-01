<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add campaign_id to promotion_page_settings
        Schema::table('promotion_page_settings', function (Blueprint $table) {
            $table->foreignId('campaign_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('promotion_campaigns')
                  ->nullOnDelete();
        });

        // Add campaign_id to promotion_banners
        Schema::table('promotion_banners', function (Blueprint $table) {
            $table->foreignId('campaign_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('promotion_campaigns')
                  ->nullOnDelete();
        });

        // Add campaign_id to promotion_flash_sales
        Schema::table('promotion_flash_sales', function (Blueprint $table) {
            $table->foreignId('campaign_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('promotion_campaigns')
                  ->nullOnDelete();
        });

        // Add campaign_id to promotion_terms
        Schema::table('promotion_terms', function (Blueprint $table) {
            $table->foreignId('campaign_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('promotion_campaigns')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('promotion_terms', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->dropColumn('campaign_id');
        });

        Schema::table('promotion_flash_sales', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->dropColumn('campaign_id');
        });

        Schema::table('promotion_banners', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->dropColumn('campaign_id');
        });

        Schema::table('promotion_page_settings', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->dropColumn('campaign_id');
        });
    }
};
