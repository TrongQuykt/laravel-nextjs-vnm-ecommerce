<?php

namespace App\Services\MarketingEngine\Rewards;

use App\Services\MarketingEngine\Contracts\RewardStrategy;
use App\Services\MarketingEngine\DTO\EnrichedCart;

/**
 * reward_type: discount_amount
 * value: {"amount": 30000}
 */
class DiscountAmountReward implements RewardStrategy
{
    public function apply(EnrichedCart $cart, array $value, string $ruleLabel = '', int $ruleId = 0): EnrichedCart
    {
        $amount = (float) ($value['amount'] ?? 0);

        $cart->addDiscount(
            "{$ruleLabel} (-" . number_format($amount, 0, ',', '.') . "đ)",
            $amount
        );

        return $cart;
    }
}
