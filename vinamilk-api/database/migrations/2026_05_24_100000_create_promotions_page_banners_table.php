<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions_page_banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('image_path');
            $table->enum('layout_slot', ['hero', 'side', 'extra'])->default('side');
            $table->enum('type', ['link', 'modal'])->default('link');
            $table->string('link_url')->nullable();
            $table->string('button_text')->nullable();
            $table->string('modal_title')->nullable();
            $table->text('modal_content')->nullable();
            $table->string('modal_image_path')->nullable();
            $table->json('modal_table_data')->nullable();
            $table->unsignedInteger('modal_products_limit')->default(9);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        if (Schema::hasColumn('promotion_banners', 'is_shown_on_promotions_page')) {
            $legacy = DB::table('promotion_banners')
                ->where('is_shown_on_promotions_page', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            foreach ($legacy as $index => $row) {
                $layoutSlot = match (true) {
                    $index === 0 => 'hero',
                    $index <= 3 => 'side',
                    default => 'extra',
                };

                DB::table('promotions_page_banners')->insert([
                    'title'                 => $row->title,
                    'subtitle'              => $row->subtitle ?? null,
                    'image_path'            => $row->image_path,
                    'layout_slot'           => $layoutSlot,
                    'type'                  => $row->type,
                    'link_url'              => $row->link_url,
                    'button_text'           => $row->button_text ?? null,
                    'modal_title'           => $row->modal_title,
                    'modal_content'         => $row->modal_content,
                    'modal_image_path'      => $row->modal_image_path ?? null,
                    'modal_table_data'      => $row->modal_table_data,
                    'modal_products_limit'  => $row->modal_products_limit ?? 9,
                    'sort_order'            => $row->sort_order,
                    'is_active'             => $row->is_active,
                    'created_at'            => $row->created_at,
                    'updated_at'            => $row->updated_at,
                ]);
            }

            DB::table('promotion_banners')
                ->where('is_shown_on_promotions_page', true)
                ->delete();

            Schema::table('promotion_banners', function (Blueprint $table) {
                $table->dropColumn('is_shown_on_promotions_page');
            });
        }
    }

    public function down(): void
    {
        Schema::table('promotion_banners', function (Blueprint $table) {
            if (! Schema::hasColumn('promotion_banners', 'is_shown_on_promotions_page')) {
                $table->boolean('is_shown_on_promotions_page')->default(false)->after('is_active');
            }
        });

        Schema::dropIfExists('promotions_page_banners');
    }
};
