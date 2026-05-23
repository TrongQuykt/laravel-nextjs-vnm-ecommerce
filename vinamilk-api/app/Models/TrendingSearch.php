<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrendingSearch extends Model
{
    protected $fillable = ['tenant_id', 'keyword', 'sort_order', 'is_active'];
}
