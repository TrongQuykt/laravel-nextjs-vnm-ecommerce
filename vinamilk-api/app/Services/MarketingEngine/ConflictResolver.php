<?php

namespace App\Services\MarketingEngine;

use Illuminate\Support\Collection;

class ConflictResolver
{
    /**
     * Resolves which rules should actually be applied given priority,
     * exclusive_group, and is_stackable constraints.
     *
     * Algorithm:
     * 1. Within each exclusive_group: keep only the highest-priority rule.
     * 2. Sort remaining rules by priority (ascending = most important first).
     * 3. Walk the sorted list; stop at the first non-stackable rule (inclusive).
     */
    public function resolve(Collection $passedRules): Collection
    {
        // Step 1: Handle exclusive groups
        $resolved = $passedRules
            ->groupBy(fn($rule) => $rule->exclusive_group ?? '__rule_' . $rule->id)
            ->map(fn(Collection $group) => $group->sortBy('priority')->first())
            ->values()
            ->sortBy('priority');

        // Step 2: Handle is_stackable — stop at first non-stackable (inclusive)
        $final = collect();
        foreach ($resolved as $rule) {
            $final->push($rule);
            if (!$rule->is_stackable) {
                break; // This rule blocks all lower-priority rules
            }
        }

        return $final;
    }
}
