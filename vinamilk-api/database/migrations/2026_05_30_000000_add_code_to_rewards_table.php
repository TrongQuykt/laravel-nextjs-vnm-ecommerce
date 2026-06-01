<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            $table->string('code')->nullable()->after('type')->unique();
        });

        // Generate codes for existing rewards
        $rewards = \DB::table('rewards')->get();
        foreach ($rewards as $reward) {
            $prefix = $reward->type === 'gift' ? 'GF-' : 'VC-';
            $randomPart = strtoupper(substr(md5($reward->id . time() . rand()), 0, 8));
            $code = $prefix . $randomPart;
            
            \DB::table('rewards')
                ->where('id', $reward->id)
                ->update(['code' => $code]);
        }
    }

    public function down(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
