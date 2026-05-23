<?php

namespace App\Services\MarketingEngine\Rewards;

use App\Services\MarketingEngine\Contracts\RewardStrategy;
use App\Services\MarketingEngine\DTO\EnrichedCart;

/**
 * reward_type: discount_product
 * value: {"product_id": 12, "percent": 50}
 */
class DiscountProductReward implements RewardStrategy
{
    public function apply(EnrichedCart $cart, array $value, string $ruleLabel = '', int $ruleId = 0): EnrichedCart
    {
        $productId = (int) $value['product_id'];
        $percent   = (float) ($value['percent'] ?? 0);

        $productSubtotal = array_sum(array_map(
            fn($item) => $item->product_id === $productId ? $item->subtotal() : 0,
            $cart->items
        ));

        if ($productSubtotal > 0) {
            $discount = $productSubtotal * ($percent / 100);
            $cart->addDiscount("{$ruleLabel} (SP #{$productId} -{$percent}%)", $discount);
        }

        return $cart;
    }
}
