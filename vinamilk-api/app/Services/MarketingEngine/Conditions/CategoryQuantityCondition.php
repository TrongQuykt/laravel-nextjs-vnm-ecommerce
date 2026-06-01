<?php

namespace App\Services\MarketingEngine\Conditions;

use App\Services\MarketingEngine\Contracts\ConditionStrategy;
use App\Services\MarketingEngine\DTO\CartPayload;

/**
 * Condition: category_subtotal
 * value: {"category_id": 3, "amount": 200000}
 */
class CategorySubtotalCondition implements ConditionStrategy
{
    public function evaluate(CartPayload $cart, string $operator, array $value): bool
    {
        $categoryId = (int) $value['category_id'];
        $amount     = (float) $value['amount'];
        $subtotal   = $cart->subtotalByCategory($categoryId);

        return match ($operator) {
            '>='    => $subtotal >= $amount,
            '>'     => $subtotal > $amount,
            '<='    => $subtotal <= $amount,
            '<'     => $subtotal < $amount,
            '='     => $subtotal == $amount,
            default => false,
        };
    }
}
