<?php

namespace App\Services\MarketingEngine\Contracts;

use App\Services\MarketingEngine\DTO\CartPayload;

interface ConditionStrategy
{
    /**
     * @param  CartPayload $cart
     * @param  string      $operator One of: =, !=, >, >=, <, <=, in, not_in, between
     * @param  array       $value    Decoded JSON from marketing_rule_conditions.value
     * @return bool
     */
    public function evaluate(CartPayload $cart, string $operator, array $value): bool;
}
