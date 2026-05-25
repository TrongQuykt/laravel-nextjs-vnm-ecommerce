<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('care_products', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('id')->constrained('products')->cascadeOnDelete();
        });

        DB::table('care_products')->orderBy('id')->each(function ($row) {
            $productId = DB::table('product_variants')->where('id', $row->product_variant_id)->value('product_id');
            if ($productId) {
                DB::table('care_products')->where('id', $row->id)->update(['product_id' => $productId]);
            }
        });

        // Số lần giao chỉ quyết định số kỳ — không dùng % giảm thêm (giá Care đã tính ở sản phẩm)
        DB::table('care_delivery_options')->update(['discount_percent' => 0]);
    }

    public function down(): void
    {
        Schema::table('care_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
        });
    }
};
