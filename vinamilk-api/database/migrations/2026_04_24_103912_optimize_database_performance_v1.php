<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->index('discount_percentage');
            $table->index('is_active');
            $table->index('product_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('promotion_banners', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('promotion_banners', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['sort_order']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex(['discount_percentage']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['product_id']);
        });
    }
};
