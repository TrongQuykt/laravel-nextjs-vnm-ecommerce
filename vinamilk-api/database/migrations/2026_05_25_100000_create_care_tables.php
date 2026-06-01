<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('care_page_settings', function (Blueprint $table) {
            $table->id();
            $table->string('tagline')->nullable();
            $table->text('intro_text')->nullable();
            $table->string('hero_image_path')->nullable();
            $table->json('benefits')->nullable();
            $table->boolean('premium_coming_soon')->default(true);
            $table->timestamps();
        });

        Schema::create('care_delivery_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('delivery_count');
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('care_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('gift_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->unsignedInteger('fixed_quantity')->default(1);
            $table->decimal('care_price_override', 15, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('care_greeting_cards', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('preview_image_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('care_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tier')->default('standard');
            $table->unsignedTinyInteger('delivery_count');
            $table->foreignId('product_variant_id')->constrained('product_variants');
            $table->foreignId('gift_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->unsignedInteger('quantity_per_delivery')->default(1);
            $table->boolean('include_greeting_card')->default(false);
            $table->foreignId('greeting_card_id')->nullable()->constrained('care_greeting_cards')->nullOnDelete();
            $table->decimal('unit_price', 15, 2);
            $table->decimal('package_subtotal', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->date('first_delivery_date');
            $table->json('shipping_address');
            $table->foreignId('payment_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('status')->default('pending_payment');
            $table->timestamps();
        });

        Schema::create('care_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('care_subscription_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('delivery_index');
            $table->date('scheduled_date');
            $table->boolean('includes_gift')->default(false);
            $table->boolean('includes_greeting_card')->default(false);
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('scheduled');
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_type')->default('standard')->after('order_number');
            $table->foreignId('care_subscription_id')->nullable()->after('order_type')
                ->constrained('care_subscriptions')->nullOnDelete();
        });

        DB::table('care_page_settings')->insert([
            'tagline' => 'Yêu thương là hiện diện mỗi ngày, bằng mọi cách.',
            'intro_text' => 'Bạn muốn chăm sóc ba mẹ hay người thân nhưng sợ bận rộn rồi quên? Với Vinamilk Care, bạn chỉ cần chọn gói định kỳ 3, 6 hoặc 9 tháng 1 lần duy nhất.',
            'benefits' => json_encode([
                ['title' => 'Sữa xịn, giá tốt nhất', 'description' => 'Mức giá tốt độc quyền cho gói Vinamilk Care.'],
                ['title' => 'Giao tận tay, đều đặn', 'description' => 'Giao đến người thân mỗi tháng 1 lần, miễn phí vận chuyển.'],
                ['title' => 'Chăm gọi điện, tư vấn', 'description' => 'Thăm hỏi và tư vấn sức khỏe mỗi 2 tuần.'],
            ]),
            'premium_coming_soon' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ([['delivery_count' => 3, 'discount_percent' => 0, 'sort_order' => 1],
                  ['delivery_count' => 6, 'discount_percent' => 0, 'sort_order' => 2],
                  ['delivery_count' => 9, 'discount_percent' => 0, 'sort_order' => 3]] as $opt) {
            DB::table('care_delivery_options')->insert(array_merge($opt, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('care_subscription_id');
            $table->dropColumn('order_type');
        });
        Schema::dropIfExists('care_deliveries');
        Schema::dropIfExists('care_subscriptions');
        Schema::dropIfExists('care_greeting_cards');
        Schema::dropIfExists('care_products');
        Schema::dropIfExists('care_delivery_options');
        Schema::dropIfExists('care_page_settings');
    }
};
