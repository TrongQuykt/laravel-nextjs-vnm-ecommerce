<?php

namespace App\Services\MarketingEngine;

use App\Services\MarketingEngine\DTO\CartPayload;
use App\Services\MarketingEngine\DTO\EnrichedCart;

class MarketingEngineService
{
    public function __construct(
        private readonly RuleLoader         $loader,
        private readonly ConditionEvaluator $evaluator,
        private readonly ConflictResolver   $resolver,
        private readonly RewardApplicator   $applicator,
    ) {}

    /**
     * Main entry point. Accepts CartPayload, returns enriched cart
     * with gifts, discounts, and applied rule metadata.
     */
    public function evaluate(CartPayload $cart): EnrichedCart
    {
        $enrichedCart = EnrichedCart::fromPayload($cart);

        // 1. Load active rules (cached) + quick pre-filter
        $rules = $this->loader->loadActiveRules($cart);

        // 2. Full condition evaluation
        $passedRulesWithGroups = $rules->map(fn($rule) => [
            'rule' => $rule,
            'passedGroups' => $this->evaluator->validateConditions($cart, $rule->conditions, $rule->condition_logic ?? 'AND')
        ])->filter(fn($res) => !empty($res['passedGroups']));

        // 3. Resolve conflicts (priority, exclusive group, stackable)
        // ConflictResolver needs Collection of Rules.
        $passedRules = $passedRulesWithGroups->pluck('rule');
        $finalRules = $this->resolver->resolve($passedRules);

        // 4. Apply rewards in priority order
        foreach ($finalRules as $rule) {
            $groupInfo = $passedRulesWithGroups->first(fn($res) => $res['rule']->id === $rule->id);
            $passedGroups = $groupInfo['passedGroups'] ?? [1];

            $enrichedCart = $this->applicator->applyRewards($enrichedCart, $rule, $passedGroups);
            $enrichedCart->applied_rules[] = [
                'id'          => $rule->id,
                'name'        => $rule->name,
                'description' => $rule->description,
            ];
        }

        // 5. Cap discounts, compute final total
        $enrichedCart->calculateFinalTotal();

        return $enrichedCart;
    }
}
