<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->foreignId('packaging_type_id')->nullable()->after('volume_id')->constrained()->nullOnDelete();
            $table->decimal('base_price', 12, 2)->nullable()->after('price');
            $table->integer('discount_percentage')->default(0)->after('base_price');
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('packaging_type_id');
            $table->dropColumn(['base_price', 'discount_percentage']);
        });
    }
};
