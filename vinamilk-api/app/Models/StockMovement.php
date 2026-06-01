<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_variant_id',
        'quantity',
        'type',
        'reference_type',
        'reference_id',
        'notes',
        'warehouse_id',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Product variant that moved
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Warehouse where movement occurred
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * User who performed the movement
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if movement is import (positive quantity)
     */
    public function isImport(): bool
    {
        return $this->quantity > 0 && in_array($this->type, ['import', 'return', 'adjustment']);
    }

    /**
     * Check if movement is export (negative quantity)
     */
    public function isExport(): bool
    {
        return $this->quantity < 0 || in_array($this->type, ['export', 'damage', 'reservation']);
    }

    /**
     * Scope for imports
     */
    public function scopeImports($query)
    {
        return $query->where('type', 'import');
    }

    /**
     * Scope for exports
     */
    public function scopeExports($query)
    {
        return $query->where('type', 'export');
    }

    /**
     * Scope by reference
     */
    public function scopeByReference($query, $type, $id)
    {
        return $query->where('reference_type', $type)->where('reference_id', $id);
    }
}
