<?php

namespace App\Services\MarketingEngine;

use App\Models\MarketingRule;
use App\Services\MarketingEngine\Contracts\RewardStrategy;
use App\Services\MarketingEngine\Rewards\GiftProductReward;
use App\Services\MarketingEngine\Rewards\GiftProductChoiceReward;
use App\Services\MarketingEngine\Rewards\DiscountPercentReward;
use App\Services\MarketingEngine\Rewards\DiscountAmountReward;
use App\Services\MarketingEngine\Rewards\DiscountProductReward;
use App\Services\MarketingEngine\Rewards\FreeShippingReward;
use App\Services\MarketingEngine\Rewards\CashbackPointsReward;
use App\Services\MarketingEngine\DTO\EnrichedCart;

class RewardApplicator
{
    private array $strategies = [
        'gift_product'        => GiftProductReward::class,
        'gift_product_choice' => GiftProductChoiceReward::class,
        'discount_percent'    => DiscountPercentReward::class,
        'discount_amount'     => DiscountAmountReward::class,
        'discount_product'    => DiscountProductReward::class,
        'free_shipping'       => FreeShippingReward::class,
        'cashback_points'     => CashbackPointsReward::class,
    ];

    private array $instances = [];

    public function applyRewards(EnrichedCart $cart, MarketingRule $rule, array $passedGroups = [1]): EnrichedCart
    {
        foreach ($rule->rewards->sortBy('sort_order') as $reward) {
            // Only apply rewards that belong to a passed group.
            // If group_id is 0, we can treat it as a global reward for that rule.
            if ($reward->group_id > 0 && !in_array($reward->group_id, $passedGroups)) {
                continue;
            }

            $strategy = $this->resolveStrategy($reward->reward_type);
            if (!$strategy) continue;

            $cart = $strategy->apply($cart, $reward->value, $rule->name, $rule->id);
        }

        return $cart;
    }

    private function resolveStrategy(string $type): ?RewardStrategy
    {
        if (!isset($this->strategies[$type])) return null;

        $class = $this->strategies[$type];
        if (!isset($this->instances[$class])) {
            $this->instances[$class] = new $class();
        }

        return $this->instances[$class];
    }
}
