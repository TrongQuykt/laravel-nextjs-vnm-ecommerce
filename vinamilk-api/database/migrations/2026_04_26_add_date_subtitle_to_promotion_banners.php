<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotion_banners', function (Blueprint $table) {
            $table->string('subtitle')->nullable()->after('title');
            $table->date('start_date')->nullable()->after('subtitle');
            $table->date('end_date')->nullable()->after('start_date');
        });
    }

    public function down(): void
    {
        Schema::table('promotion_banners', function (Blueprint $table) {
            $table->dropColumn(['subtitle', 'start_date', 'end_date']);
        });
    }
};
