<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // -------------------------------------------------------
        // 1. marketing_rules — Metadata, lifecycle, conflict config
        // -------------------------------------------------------
        Schema::create('marketing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();

            // Lifecycle
            $table->boolean('is_active')->default(true);
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();

            // Conflict resolution
            $table->smallInteger('priority')->default(100);         // lower = higher priority
            $table->boolean('is_stackable')->default(false);        // false = blocks lower-priority rules
            $table->string('exclusive_group', 100)->nullable();     // only 1 rule per group wins

            // Usage limits
            $table->integer('usage_limit')->nullable();             // null = unlimited
            $table->integer('usage_count')->default(0);
            $table->integer('per_user_limit')->nullable();

            // Top-level AND/OR between condition groups
            $table->enum('condition_logic', ['AND', 'OR'])->default('AND');

            $table->timestamps();

            $table->index(['is_active', 'start_date', 'end_date', 'priority'], 'idx_rules_active_priority');
        });

        // -------------------------------------------------------
        // 2. marketing_rule_conditions
        // -------------------------------------------------------
        Schema::create('marketing_rule_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('marketing_rules')->cascadeOnDelete();

            // Grouping for complex logic: (A AND B) OR (C AND D)
            $table->tinyInteger('group_id')->default(1);
            $table->enum('group_logic', ['AND', 'OR'])->default('AND');

            // Strategy selector
            $table->enum('condition_type', [
                'cart_total',
                'cart_quantity',
                'product_in_cart',
                'product_quantity',
                'category_in_cart',
                'category_quantity',
                'category_subtotal',
                'user_segment',
                'coupon_code',
                'payment_method',
                'day_of_week',
                'time_of_day',
            ]);

            $table->enum('operator', ['=', '!=', '>', '>=', '<', '<=', 'in', 'not_in', 'between']);

            // Flexible JSON value: {"amount": 500000} / {"product_ids": [1,2]} etc.
            $table->json('value');

            $table->timestamp('created_at')->useCurrent();

            $table->index('rule_id', 'idx_conditions_rule_id');
            $table->index(['rule_id', 'condition_type'], 'idx_conditions_rule_type');
        });

        // -------------------------------------------------------
        // 3. marketing_rule_rewards
        // -------------------------------------------------------
        Schema::create('marketing_rule_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('marketing_rules')->cascadeOnDelete();

            $table->enum('reward_type', [
                'gift_product',
                'gift_product_choice',
                'discount_percent',
                'discount_amount',
                'discount_product',
                'discount_category',
                'free_shipping',
                'cashback_points',
            ]);

            // Flexible JSON:
            // gift_product        : {"product_id": 99, "variant_id": 5, "quantity": 2}
            // discount_percent    : {"percent": 10, "max_discount": 50000}
            // discount_amount     : {"amount": 30000}
            // discount_product    : {"product_id": 12, "percent": 50}
            // discount_category   : {"category_id": 3, "percent": 15}
            // free_shipping       : {}
            // cashback_points     : {"points": 100}
            $table->json('value');

            $table->tinyInteger('sort_order')->default(1);

            $table->timestamp('created_at')->useCurrent();

            $table->index('rule_id', 'idx_rewards_rule_id');
        });

        // -------------------------------------------------------
        // 4. marketing_rule_user_usage — Per-user limit tracking
        // -------------------------------------------------------
        Schema::create('marketing_rule_user_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('marketing_rules')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamp('used_at')->useCurrent();

            $table->index(['rule_id', 'user_id'], 'idx_usage_rule_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_rule_user_usage');
        Schema::dropIfExists('marketing_rule_rewards');
        Schema::dropIfExists('marketing_rule_conditions');
        Schema::dropIfExists('marketing_rules');
    }
};
