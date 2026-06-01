<?php

namespace App\Services\MarketingEngine\Conditions;

use App\Services\MarketingEngine\Contracts\ConditionStrategy;
use App\Services\MarketingEngine\DTO\CartPayload;

/**
 * Condition: product_in_cart
 * value: {"product_ids": [12, 45, 78]}
 * operator: "in" = at least one of these is in cart
 *           "not_in" = none of these is in cart
 */
class ProductInCartCondition implements ConditionStrategy
{
    public function evaluate(CartPayload $cart, string $operator, array $value): bool
    {
        $cartIds     = $cart->productIds();
        $requiredIds = (array) ($value['product_ids'] ?? []);

        $intersection = array_intersect($requiredIds, $cartIds);

        return match ($operator) {
            'in'     => count($intersection) > 0,
            'not_in' => count($intersection) === 0,
            '='      => count($intersection) === count($requiredIds), // ALL required products
            default  => false,
        };
    }
}
