<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mega_menus', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Menu name (e.g., Sản phẩm)');
            $table->string('url')->nullable()->comment('URL for the menu item');
            $table->foreignId('featured_product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->json('columns')->nullable()->comment('JSON structure for columns and links');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mega_menus');
    }
};
