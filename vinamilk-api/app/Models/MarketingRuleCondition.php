<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingRuleCondition extends Model
{
    public $timestamps = false;
    protected $table = 'marketing_rule_conditions';

    protected $fillable = [
        'rule_id', 'group_id', 'group_logic',
        'condition_type', 'operator', 'value',
    ];

    protected $casts = [
        'value'    => 'array',
        'group_id' => 'integer',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(MarketingRule::class, 'rule_id');
    }
}
