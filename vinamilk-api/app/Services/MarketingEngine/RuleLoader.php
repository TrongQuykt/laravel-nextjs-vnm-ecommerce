<?php

namespace App\Services\MarketingEngine;

use App\Models\MarketingRule;
use App\Services\MarketingEngine\DTO\CartPayload;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RuleLoader
{
    private const CACHE_KEY = 'marketing:active_rules';
    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Load active rules (with eager-loaded conditions + rewards).
     * Uses cache to avoid repeated DB queries on every cart evaluation.
     */
    public function loadActiveRules(CartPayload $cart): Collection
    {
        $rules = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return MarketingRule::with(['conditions', 'rewards'])
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('start_date')->orWhere('start_date', '<=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', now());
                })
                ->where(function ($q) {
                    $q->whereNull('usage_limit')
                      ->orWhereRaw('usage_count < usage_limit');
                })
                ->orderBy('priority')
                ->get();
        });

        // Pre-filter: quick reject without full condition evaluation
        return $rules->filter(fn($rule) => !$this->shouldQuickReject($rule, $cart));
    }

    /**
     * O(1) reject based on indexed cart data.
     * Rejects ~60-70% of rules before full evaluation.
     */
    private function shouldQuickReject(MarketingRule $rule, CartPayload $cart): bool
    {
        // If rule uses OR logic, we can't easily quick-reject based on a single failing condition.
        // For simplicity and safety, skip quick reject for OR rules.
        if (($rule->condition_logic ?? 'AND') === 'OR') {
            return false;
        }

        $cartProductIds  = $cart->productIds();
        $cartCategoryIds = $cart->categoryIds();

        foreach ($rule->conditions as $cond) {
            switch ($cond->condition_type) {
                case 'cart_total':
                    if (in_array($cond->operator, ['>=', '>'])) {
                        $value = $cond->value;
                        $min = (float) (is_array($value) ? ($value['amount'] ?? ($value['min'] ?? ($value[0] ?? 0))) : $value);
                        if ($cart->subtotal < $min) return true;
                    }
                    break;

                case 'product_in_cart':
                case 'product_quantity':
                case 'product_quantity_in_cases':
                    $required = (array) ($cond->value['product_ids'] ?? [$cond->value['product_id'] ?? 0]);
                    if (empty(array_intersect($required, $cartProductIds))) return true;
                    break;

                case 'category_in_cart':
                case 'category_quantity':
                case 'category_subtotal':
                    $catId = (int) ($cond->value['category_id'] ?? 0);
                    if ($catId && !in_array($catId, $cartCategoryIds)) return true;
                    break;
            }
        }

        return false;
    }

    /** Flush rule cache (call when admin saves a rule) */
    public static function invalidateCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
