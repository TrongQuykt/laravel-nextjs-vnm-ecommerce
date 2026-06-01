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
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('image')->nullable()->after('variant_name');
            $table->string('volume')->nullable()->after('image');
            $table->string('packing_type')->nullable()->after('volume');
            $table->decimal('original_price', 15, 2)->nullable()->after('packing_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['image', 'volume', 'packing_type', 'original_price']);
        });
    }
};
