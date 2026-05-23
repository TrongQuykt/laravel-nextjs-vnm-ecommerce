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
        Schema::table('banners', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('tenant_id')->constrained()->nullOnDelete();
            $table->text('box_text')->nullable()->after('link');
            $table->string('box_subtitle')->nullable()->after('box_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id', 'box_text', 'box_subtitle']);
        });
    }
};
