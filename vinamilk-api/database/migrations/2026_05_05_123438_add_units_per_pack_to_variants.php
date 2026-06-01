<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('units_per_pack')->default(1)->after('units_per_case')->comment('Số lượng hộp/chai lẻ trong 1 đơn vị bán (VD: 1 lốc = 4, 1 khay = 24)');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('units_per_pack');
        });
    }
};
