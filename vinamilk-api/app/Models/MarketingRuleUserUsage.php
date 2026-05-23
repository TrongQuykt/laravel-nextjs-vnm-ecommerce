<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingRuleUserUsage extends Model
{
    public $timestamps = false;
    protected $table = 'marketing_rule_user_usage';

    protected $fillable = ['rule_id', 'user_id', 'order_id'];

    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(MarketingRule::class, 'rule_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
