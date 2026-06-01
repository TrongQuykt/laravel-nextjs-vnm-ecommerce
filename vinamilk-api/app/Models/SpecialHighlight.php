<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SpecialHighlight extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'icon',
    ];

    /**
     * The products that belong to the highlight.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_special_highlight');
    }
}
