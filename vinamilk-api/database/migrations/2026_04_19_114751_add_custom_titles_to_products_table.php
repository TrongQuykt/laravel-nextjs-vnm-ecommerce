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
            $table->string('features_title')->nullable()->after('description');
            $table->string('description_title')->nullable()->after('features_title');
            $table->string('comparison_title')->nullable()->after('description_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['features_title', 'description_title', 'comparison_title']);
        });
    }
};
