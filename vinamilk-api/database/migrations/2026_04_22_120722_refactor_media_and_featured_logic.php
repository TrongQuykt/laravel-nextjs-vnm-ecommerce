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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('home_featured_variant_id')->nullable()->after('is_home_featured')->constrained('product_variants')->nullOnDelete();
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('position')->default(0)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['home_featured_variant_id']);
            $table->dropColumn('home_featured_variant_id');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('position');
        });
    }
};
