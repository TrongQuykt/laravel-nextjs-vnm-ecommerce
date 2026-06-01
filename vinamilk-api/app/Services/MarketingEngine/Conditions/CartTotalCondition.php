<?php

namespace App\Services\MarketingEngine\Conditions;

use App\Services\MarketingEngine\Contracts\ConditionStrategy;
use App\Services\MarketingEngine\DTO\CartPayload;

/**
 * Condition: cart_total
 * value: {"amount": 500000}  |  {"min": 200000, "max": 500000}
 */
class CartTotalCondition implements ConditionStrategy
{
    public function evaluate(CartPayload $cart, string $operator, mixed $value): bool
    {
        $amount = $this->resolveAmount($value);

        return match ($operator) {
            '>='      => $cart->subtotal >= $amount,
            '>'       => $cart->subtotal > $amount,
            '<='      => $cart->subtotal <= $amount,
            '<'       => $cart->subtotal < $amount,
            '='       => $cart->subtotal == $amount,
            'between' => $cart->subtotal >= ($value['min'] ?? 0) && $cart->subtotal <= ($value['max'] ?? 0),
            default   => false,
        };
    }

    private function resolveAmount(mixed $value): float
    {
        if (is_array($value)) {
            return (float) ($value['amount'] ?? ($value[0] ?? 0));
        }
        return (float) $value;
    }
}
