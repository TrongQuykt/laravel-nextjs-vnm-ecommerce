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
        Schema::create('categories', function (Blueprint $刻) {
            $刻->id();
            $刻->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $刻->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $刻->string('name');
            $刻->string('slug');
            $刻->text('description')->nullable();
            $刻->string('image')->nullable();
            $刻->boolean('is_active')->default(true);
            $刻->timestamps();

            $刻->unique(['tenant_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
