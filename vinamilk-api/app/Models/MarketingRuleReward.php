<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingRuleReward extends Model
{
    public $timestamps = false;
    protected $table = 'marketing_rule_rewards';

    protected $fillable = [
        'rule_id', 'group_id', 'reward_type', 'value', 'sort_order',
    ];

    protected $casts = [
        'group_id'   => 'integer',
        'value'      => 'array',
        'sort_order' => 'integer',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(MarketingRule::class, 'rule_id');
    }
}
