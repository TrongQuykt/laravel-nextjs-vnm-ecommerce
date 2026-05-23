<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Xóa cột ở bảng products
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('units_per_case');
        });

        // Thêm cột vào bảng product_variants
        Schema::table('product_variants', function (Blueprint $table) {
            $table->integer('units_per_case')->default(1)->comment('Số lượng đơn vị cơ bản trong 1 thùng của biến thể này');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn('units_per_case');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->integer('units_per_case')->default(1);
        });
    }
};
