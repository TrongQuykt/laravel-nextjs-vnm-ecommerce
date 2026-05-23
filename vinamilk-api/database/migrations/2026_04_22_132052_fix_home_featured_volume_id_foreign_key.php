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
            // Xóa khóa ngoại cũ (Laravel giữ tên cũ sau khi rename cột)
            $table->dropForeign('products_home_featured_variant_id_foreign');
            
            // Thêm khóa ngoại mới trỏ tới bảng media theo dung tích
            $table->foreign('home_featured_volume_id')
                ->references('id')
                ->on('product_volume_media')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['home_featured_volume_id']);
            
            $table->foreign('home_featured_volume_id')
                ->references('id')
                ->on('product_variants')
                ->onDelete('set null');
        });
    }
};
