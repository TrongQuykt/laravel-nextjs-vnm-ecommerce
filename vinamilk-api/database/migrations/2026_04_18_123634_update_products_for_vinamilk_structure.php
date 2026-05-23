<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('sector_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_line_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sugar_level_id')->nullable()->constrained()->nullOnDelete();
            
            $table->json('nutrition_facts')->nullable();
            $table->text('ingredients')->nullable();
            $table->text('usage_instructions')->nullable();
            $table->text('storage_instructions')->nullable();
            
            // Clean up old fields if any (keep name/slug)
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->foreignId('flavor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('volume_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('volume_id');
            $table->dropConstrainedForeignId('flavor_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sugar_level_id');
            $table->dropConstrainedForeignId('product_line_id');
            $table->dropConstrainedForeignId('brand_id');
            $table->dropConstrainedForeignId('sector_id');
            
            $table->dropColumn(['nutrition_facts', 'ingredients', 'usage_instructions', 'storage_instructions']);
        });
    }
};
