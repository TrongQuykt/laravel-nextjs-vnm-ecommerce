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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_method_name')->nullable()->after('shipping_method_id');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('package_number')->nullable()->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('shipping_method_name');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('package_number');
        });
    }
};
