<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotion_banners', function (Blueprint $table) {
            $table->json('modal_table_data')->nullable()->after('modal_content');
        });
    }

    public function down(): void
    {
        Schema::table('promotion_banners', function (Blueprint $table) {
            $table->dropColumn('modal_table_data');
        });
    }
};
