<?php

namespace App\Services\MarketingEngine\DTO;

class CartItem
{
    public function __construct(
        public readonly int   $product_id,
        public readonly int   $category_id,
        public readonly int   $variant_id,
        public readonly int   $quantity,
        public readonly float $price,           // unit price
    ) {}

    public function subtotal(): float
    {
        return $this->price * $this->quantity;
    }
}
