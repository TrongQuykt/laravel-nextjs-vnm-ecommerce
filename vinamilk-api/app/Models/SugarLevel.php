<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class SugarLevel extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'slug', 'sort_order'];
}
