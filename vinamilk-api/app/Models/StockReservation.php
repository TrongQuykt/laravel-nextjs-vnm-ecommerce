<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockReservation extends Model
{
    protected $fillable = [
        'product_variant_id',
        'order_number',
        'quantity',
        'reserved_at',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
        'expires_at' => 'datetime',
        'quantity' => 'integer',
    ];

    /**
     * Product variant that is reserved
     */
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Check if reservation is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if reservation is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    /**
     * Scope for pending reservations
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending')->where('expires_at', '>', now());
    }

    /**
     * Scope for expired reservations
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'pending')->where('expires_at', '<', now());
    }
}
