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
        Schema::create('products', function (Blueprint $刻) {
            $刻->id();
            $刻->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $刻->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $刻->string('name');
            $刻->string('slug');
            $刻->text('short_description')->nullable();
            $刻->longText('description')->nullable();
            $刻->string('brand')->nullable();
            $刻->string('status')->default('draft'); # draft, published, archived
            $刻->json('metadata')->nullable();
            $刻->timestamps();

            $刻->unique(['tenant_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
