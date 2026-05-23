<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingRule extends Model
{
    protected $fillable = [
        'name', 'description',
        'is_active', 'start_date', 'end_date',
        'priority', 'is_stackable', 'exclusive_group',
        'usage_limit', 'usage_count', 'per_user_limit',
        'condition_logic',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'is_stackable' => 'boolean',
        'start_date'   => 'datetime',
        'end_date'     => 'datetime',
        'usage_limit'  => 'integer',
        'usage_count'  => 'integer',
        'per_user_limit' => 'integer',
        'priority'     => 'integer',
    ];

    public function conditions(): HasMany
    {
        return $this->hasMany(MarketingRuleCondition::class, 'rule_id');
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(MarketingRuleReward::class, 'rule_id')->orderBy('sort_order');
    }

    public function userUsages(): HasMany
    {
        return $this->hasMany(MarketingRuleUserUsage::class, 'rule_id');
    }

    /** Check if this rule is within its active time window */
    public function isWithinDateRange(): bool
    {
        $now = now();
        if ($this->start_date && $now->lt($this->start_date)) return false;
        if ($this->end_date && $now->gt($this->end_date)) return false;
        return true;
    }

    /** Check global usage limit */
    public function hasUsagesLeft(): bool
    {
        if (is_null($this->usage_limit)) return true;
        return $this->usage_count < $this->usage_limit;
    }
}
