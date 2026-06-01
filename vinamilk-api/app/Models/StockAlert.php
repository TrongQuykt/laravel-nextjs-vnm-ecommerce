<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAlert extends Model
{
    protected $fillable = [
        'product_variant_id',
        'type',
        'current_quantity',
        'threshold',
        'is_resolved',
        'resolved_at',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
        'current_quantity' => 'integer',
        'threshold' => 'integer',
    ];

    /**
     * Product variant that triggered the alert
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Scope for unresolved alerts
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    /**
     * Scope for resolved alerts
     */
    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }

    /**
     * Scope by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Mark alert as resolved
     */
    public function markAsResolved(): void
    {
        $this->update([
            'is_resolved' => true,
            'resolved_at' => now(),
        ]);
    }

    /**
     * Get variant display name
     */
    public function getVariantDisplayNameAttribute(): string
    {
        $variant = $this->productVariant;
        if (!$variant) return 'N/A';

        $parts = [];
        if ($variant->volume && $variant->volume->name) $parts[] = $variant->volume->name;
        if ($variant->packagingType && $variant->packagingType->name) $parts[] = $variant->packagingType->name;

        $result = implode(' - ', array_filter($parts));
        return $result ?: 'Variant #' . $variant->id;
    }
}
