<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('care_products', function (Blueprint $table) {
            $table->dropForeign(['product_variant_id']);
            $table->dropForeign(['gift_variant_id']);
        });

        Schema::table('care_products', function (Blueprint $table) {
            $table->dropColumn([
                'product_variant_id',
                'gift_variant_id',
                'fixed_quantity',
                'care_price_override',
                'sort_order',
            ]);
        });

        Schema::table('care_products', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('care_products', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->change();
            $table->foreignId('product_variant_id')->after('id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('gift_variant_id')->nullable()->after('product_variant_id')->constrained('product_variants')->nullOnDelete();
            $table->unsignedInteger('fixed_quantity')->default(1);
            $table->decimal('care_price_override', 15, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
        });
    }
};
