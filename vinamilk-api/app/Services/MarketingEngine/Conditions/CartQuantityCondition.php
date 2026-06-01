<?php

namespace App\Services\MarketingEngine\Conditions;

use App\Services\MarketingEngine\Contracts\ConditionStrategy;
use App\Services\MarketingEngine\DTO\CartPayload;

/**
 * Condition: cart_quantity
 * value: {"quantity": 3}
 */
class CartQuantityCondition implements ConditionStrategy
{
    public function evaluate(CartPayload $cart, string $operator, array $value): bool
    {
        $total = $cart->totalQuantity();
        $qty   = (int) $value['quantity'];

        return match ($operator) {
            '>='    => $total >= $qty,
            '>'     => $total > $qty,
            '<='    => $total <= $qty,
            '<'     => $total < $qty,
            '='     => $total === $qty,
            default => false,
        };
    }
}
