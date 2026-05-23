<?php

namespace App\Services\MarketingEngine\Conditions;

use App\Services\MarketingEngine\Contracts\ConditionStrategy;
use App\Services\MarketingEngine\DTO\CartPayload;

/**
 * Condition: category_quantity
 * value: {"category_id": 3, "quantity": 2}
 */
class CategoryQuantityCondition implements ConditionStrategy
{
    public function evaluate(CartPayload $cart, string $operator, array $value): bool
    {
        $categoryId = (int) $value['category_id'];
        $qty        = (int) $value['quantity'];
        $cartQty    = $cart->quantityByCategory($categoryId);

        return match ($operator) {
            '>='    => $cartQty >= $qty,
            '>'     => $cartQty > $qty,
            '<='    => $cartQty <= $qty,
            '<'     => $cartQty < $qty,
            '='     => $cartQty === $qty,
            default => false,
        };
    }
}
