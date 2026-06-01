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
        Schema::table('categories', function (Blueprint $table) {
            $table->decimal('loyalty_rate', 5, 2)->nullable()->after('slug')->comment('Tỷ lệ tích điểm %');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('loyalty_rate', 5, 2)->nullable()->after('name')->comment('Tỷ lệ tích điểm % (Ghi đè danh mục)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('loyalty_rate');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('loyalty_rate');
        });
    }
};
