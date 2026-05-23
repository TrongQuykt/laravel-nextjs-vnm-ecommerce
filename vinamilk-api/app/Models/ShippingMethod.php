<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'name',
        'provider',
        'base_cost',
        'is_active',
        'tenant_id',
    ];
}
