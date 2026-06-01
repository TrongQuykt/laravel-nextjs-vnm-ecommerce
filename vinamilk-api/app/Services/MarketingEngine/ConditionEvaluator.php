<?php

namespace App\Services\MarketingEngine;

use App\Models\MarketingRule;
use App\Services\MarketingEngine\Contracts\ConditionStrategy;
use App\Services\MarketingEngine\Conditions\CartTotalCondition;
use App\Services\MarketingEngine\Conditions\CartQuantityCondition;
use App\Services\MarketingEngine\Conditions\ProductInCartCondition;
use App\Services\MarketingEngine\Conditions\ProductQuantityCondition;
use App\Services\MarketingEngine\Conditions\ProductQuantityInCasesCondition;
use App\Services\MarketingEngine\Conditions\CategoryInCartCondition;
use App\Services\MarketingEngine\Conditions\CategoryQuantityCondition;
use App\Services\MarketingEngine\Conditions\CategorySubtotalCondition;
use App\Services\MarketingEngine\DTO\CartPayload;
use Illuminate\Support\Collection;

class ConditionEvaluator
{
    /** Map condition_type → Strategy class (no IoC overhead per call) */
    private array $strategies = [
        'cart_total'                => CartTotalCondition::class,
        'cart_quantity'             => CartQuantityCondition::class,
        'product_in_cart'           => ProductInCartCondition::class,
        'product_quantity'          => ProductQuantityCondition::class,
        'product_quantity_in_cases' => ProductQuantityInCasesCondition::class,
        'category_in_cart'          => CategoryInCartCondition::class,
        'category_quantity'         => CategoryQuantityCondition::class,
        'category_subtotal'         => CategorySubtotalCondition::class,
    ];

    /** Singleton instances to avoid repeated instantiation */
    private array $instances = [];

    /**
     * Returns an array of passed group_ids if the cart satisfies the rule's conditions.
     * Returns an empty array if the rule as a whole is not satisfied.
     *
     * Supports: (Group1_A AND Group1_B) AND/OR (Group2_C AND Group2_D)
     */
    public function validateConditions(CartPayload $cart, Collection $conditions, string $topLevelLogic = 'AND'): array
    {
        if ($conditions->isEmpty()) return [1];

        // Group by group_id
        $groups = $conditions->groupBy('group_id');

        // Evaluate each group
        $passedGroups = [];
        $allGroups = $groups->keys()->toArray();
        $groupResults = [];

        foreach ($groups as $groupId => $groupConditions) {
            $isGroupPassed = $this->evaluateGroup($cart, $groupConditions);
            $groupResults[$groupId] = $isGroupPassed;
            if ($isGroupPassed) {
                $passedGroups[] = (int) $groupId;
            }
        }

        // Top-level logic between groups
        $isValid = ($topLevelLogic === 'OR') 
            ? !empty($passedGroups) 
            : count($passedGroups) === count($allGroups);

        if ($isValid && $topLevelLogic === 'OR' && count($passedGroups) > 1) {
            // Pick the first satisfied group (lowest ID = highest priority)
            return [min($passedGroups)];
        }

        return $isValid ? $passedGroups : [];
    }

    private function evaluateGroup(CartPayload $cart, Collection $conditions): bool
    {
        // Use group_logic of first condition in this group
        $logic = strtoupper($conditions->first()->group_logic ?? 'AND');

        foreach ($conditions as $condition) {
            $strategy = $this->resolveStrategy($condition->condition_type);
            if (!$strategy) continue; // Unknown type — skip, don't fail

            $result = $strategy->evaluate($cart, $condition->operator, $condition->value);

            if ($logic === 'AND' && !$result) return false; // Short-circuit AND
            if ($logic === 'OR'  &&  $result) return true;  // Short-circuit OR
        }

        // AND: all passed. OR: none passed.
        return $logic === 'AND';
    }

    private function resolveStrategy(string $type): ?ConditionStrategy
    {
        if (!isset($this->strategies[$type])) return null;

        $class = $this->strategies[$type];
        if (!isset($this->instances[$class])) {
            $this->instances[$class] = new $class();
        }

        return $this->instances[$class];
    }
}
