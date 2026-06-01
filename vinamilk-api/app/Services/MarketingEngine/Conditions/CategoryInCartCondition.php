<?php

namespace App\Services\MarketingEngine\Conditions;

use App\Services\MarketingEngine\Contracts\ConditionStrategy;
use App\Services\MarketingEngine\DTO\CartPayload;

/**
 * Condition: category_in_cart
 * value: {"category_id": 3}
 */
class CategoryInCartCondition implements ConditionStrategy
{
    public function evaluate(CartPayload $cart, string $operator, array $value): bool
    {
        $categoryId  = (int) $value['category_id'];
        $cartCatIds  = $cart->categoryIds();

        return match ($operator) {
            'in', '='  => in_array($categoryId, $cartCatIds),
            'not_in'   => !in_array($categoryId, $cartCatIds),
            default    => false,
        };
    }
}
