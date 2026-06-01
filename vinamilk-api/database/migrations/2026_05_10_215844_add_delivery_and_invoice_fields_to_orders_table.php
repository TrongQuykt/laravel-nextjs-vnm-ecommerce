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
            $table->enum('delivery_type', ['shipping', 'pickup'])->default('shipping')->after('status');
            $table->foreignId('store_id')->nullable()->constrained('stores')->after('delivery_type');
            $table->string('pickup_time')->nullable()->after('store_id');
            $table->json('invoice_info')->nullable()->after('pickup_time');
            $table->foreignId('shipping_method_id')->nullable()->constrained('shipping_methods')->after('invoice_info');
            $table->date('expected_delivery_date')->nullable()->after('shipping_method_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropForeign(['shipping_method_id']);
            $table->dropColumn(['delivery_type', 'store_id', 'pickup_time', 'invoice_info', 'shipping_method_id', 'expected_delivery_date']);
        });
    }
};
