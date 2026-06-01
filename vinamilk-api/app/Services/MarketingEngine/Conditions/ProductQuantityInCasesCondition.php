<?php

namespace App\Services\MarketingEngine\Conditions;

use App\Services\MarketingEngine\Contracts\ConditionStrategy;
use App\Services\MarketingEngine\DTO\CartPayload;
use App\Models\ProductVariant;

/**
 * condition_type: product_quantity_in_cases
 * value: {"product_id": 12, "cases": 1}
 */
class ProductQuantityInCasesCondition implements ConditionStrategy
{
    public function evaluate(CartPayload $cart, string $operator, array $value): bool
    {
        $productId     = (int) ($value['product_id'] ?? 0);
        $requiredCases = (float) ($value['cases'] ?? 0);

        // Tính tổng số thùng dựa trên từng variant trong giỏ (Dùng hệ số quy đổi thông minh)
        $totalActualCases = 0;
        foreach ($cart->items as $item) {
            if ($item->product_id === $productId) {
                $variant = ProductVariant::find($item->variant_id);
                $unitsPerPack = $variant?->units_per_pack ?? 1;
                $unitsPerCase = $variant?->units_per_case ?? 1;
                
                // Quy đổi: (Số lượng mua * Số lẻ trong gói) / Số lẻ trong thùng
                // Ví dụ: Mua 2 gói (loại 24 hộp) / Thùng 48 = 1 thùng
                $totalActualCases += ($item->quantity * $unitsPerPack) / $unitsPerCase;
            }
        }

        return match ($operator) {
            '>='    => $totalActualCases >= $requiredCases,
            '>'     => $totalActualCases > $requiredCases,
            '<='    => $totalActualCases <= $requiredCases,
            '<'     => $totalActualCases < $requiredCases,
            '=', 'in' => abs($totalActualCases - $requiredCases) < 0.0001 || $totalActualCases >= $requiredCases,
            default => false,
        };
    }
}
