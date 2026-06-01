<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_rule_conditions', function (Blueprint $table) {
            $table->string('condition_type', 100)->change();
        });

        Schema::table('marketing_rule_rewards', function (Blueprint $table) {
            $table->string('reward_type', 100)->change();
        });
    }

    public function down(): void
    {
        Schema::table('marketing_rule_conditions', function (Blueprint $table) {
            $table->string('condition_type', 20)->change(); // Giả sử trước đó là 20
        });

        Schema::table('marketing_rule_rewards', function (Blueprint $table) {
            $table->string('reward_type', 20)->change();
        });
    }
};
