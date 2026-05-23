<?php

namespace App\Services\MarketingEngine\Contracts;

use App\Services\MarketingEngine\DTO\EnrichedCart;

interface RewardStrategy
{
    /**
     * @param  EnrichedCart $cart
     * @param  array        $value Decoded JSON from marketing_rule_rewards.value
     * @param  string       $ruleLabel Human-readable rule name for discount labels
     * @param  int          $ruleId ID of the rule being applied
     * @return EnrichedCart
     */
    public function apply(EnrichedCart $cart, array $value, string $ruleLabel = '', int $ruleId = 0): EnrichedCart;
}
