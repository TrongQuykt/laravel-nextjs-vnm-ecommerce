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
        Schema::create('blog_post_suggested', function (Blueprint $wrapper) {
            $wrapper->id();
            $wrapper->foreignId('blog_post_id')->constrained('blog_posts')->onDelete('cascade');
            $wrapper->foreignId('suggested_post_id')->constrained('blog_posts')->onDelete('cascade');
            $wrapper->timestamps();
            
            // Prevent duplicate entries
            $wrapper->unique(['blog_post_id', 'suggested_post_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_post_suggested');
    }
};
