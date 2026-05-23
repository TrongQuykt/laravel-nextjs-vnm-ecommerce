<?php

namespace App\Services\MarketingEngine\Rewards;

use App\Services\MarketingEngine\Contracts\RewardStrategy;
use App\Services\MarketingEngine\DTO\EnrichedCart;

/**
 * reward_type: cashback_points
 * value: {"points": 100}
 */
class CashbackPointsReward implements RewardStrategy
{
    public function apply(EnrichedCart $cart, array $value, string $ruleLabel = '', int $ruleId = 0): EnrichedCart
    {
        $cart->bonus_points += (int) ($value['points'] ?? 0);
        return $cart;
    }
}
