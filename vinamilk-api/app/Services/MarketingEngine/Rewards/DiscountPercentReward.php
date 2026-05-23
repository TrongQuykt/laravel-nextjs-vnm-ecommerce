<?php

namespace App\Services\MarketingEngine\Rewards;

use App\Services\MarketingEngine\Contracts\RewardStrategy;
use App\Services\MarketingEngine\DTO\EnrichedCart;

/**
 * reward_type: discount_percent
 * value: {"percent": 10, "max_discount": 50000}
 */
class DiscountPercentReward implements RewardStrategy
{
    public function apply(EnrichedCart $cart, array $value, string $ruleLabel = '', int $ruleId = 0): EnrichedCart
    {
        $percent     = (float) ($value['percent'] ?? 0);
        $maxDiscount = isset($value['max_discount']) ? (float) $value['max_discount'] : null;

        $discount = $cart->subtotal * ($percent / 100);

        if ($maxDiscount !== null) {
            $discount = min($discount, $maxDiscount);
        }

        $cart->addDiscount(
            "{$ruleLabel} (-{$percent}%)",
            $discount
        );

        return $cart;
    }
}
