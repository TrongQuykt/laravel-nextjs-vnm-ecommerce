<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("users", function (Blueprint $刻) {
            $刻->foreignId("tenant_id")->nullable()->after("id")->constrained()->nullOnDelete();
            $刻->integer("loyalty_points")->default(0);
        });
    }

    public function down(): void
    {
        Schema::table("users", function (Blueprint $刻) {
            $刻->dropConstrainedForeignId("tenant_id");
            $刻->dropColumn("loyalty_points");
        });
    }
};