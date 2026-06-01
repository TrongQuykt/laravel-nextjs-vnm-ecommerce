<?php

namespace App\Services\MarketingEngine\Conditions;

use App\Services\MarketingEngine\Contracts\ConditionStrategy;
use App\Services\MarketingEngine\DTO\CartPayload;

/**
 * Condition: product_quantity
 * value: {"product_id": 12, "quantity": 2}
 */
class ProductQuantityCondition implements ConditionStrategy
{
    public function evaluate(CartPayload $cart, string $operator, array $value): bool
    {
        $productId = (int) $value['product_id'];
        $qty       = (int) $value['quantity'];
        $cartQty   = $cart->quantityByProduct($productId);

        return match ($operator) {
            '>=', 'in' => $cartQty >= $qty,
            '>'        => $cartQty > $qty,
            '<='       => $cartQty <= $qty,
            '<'        => $cartQty < $qty,
            '='        => $cartQty === $qty,
            default    => false,
        };
    }
}
