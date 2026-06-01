<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_rule_rewards', function (Blueprint $table) {
            // Add group_id to link rewards to specific condition groups
            // default 0 means apply to any group (legacy or global)
            $table->tinyInteger('group_id')->default(1)->after('rule_id');
        });
    }

    public function down(): void
    {
        Schema::table('marketing_rule_rewards', function (Blueprint $table) {
            $table->dropColumn('group_id');
        });
    }
};
