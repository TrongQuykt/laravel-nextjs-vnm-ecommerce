<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotion_banners', function (Blueprint $table) {
            $table->integer('row_span')->default(1)->after('col_span');
        });
    }

    public function down(): void
    {
        Schema::table('promotion_banners', function (Blueprint $table) {
            $table->dropColumn('row_span');
        });
    }
};
