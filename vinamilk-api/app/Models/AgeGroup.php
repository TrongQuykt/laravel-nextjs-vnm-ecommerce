<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class AgeGroup extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'slug'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_age_group');
    }
}
