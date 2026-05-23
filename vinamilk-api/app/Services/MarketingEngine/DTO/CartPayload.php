<?php

namespace App\Services\MarketingEngine\DTO;

class CartPayload
{
    /** @param CartItem[] $items */
    public function __construct(
        public readonly array   $items,
        public readonly float   $subtotal,
        public readonly ?int    $user_id = null,
        public readonly ?string $coupon_code = null,
        public readonly ?string $payment_method = null,
        public readonly array   $reward_selections = [],
    ) {}

    public static function fromArray(array $data): self
    {
        $items = array_map(fn($i) => new CartItem(
            product_id:  (int) $i['product_id'],
            category_id: (int) ($i['category_id'] ?? 0),
            variant_id:  (int) ($i['variant_id'] ?? 0),
            quantity:    (int) $i['quantity'],
            price:       (float) $i['price'],
        ), $data['items'] ?? []);

        $subtotal = array_sum(array_map(fn(CartItem $i) => $i->subtotal(), $items)) * 1000;

        return new self(
            items:          $items,
            subtotal:       $subtotal,
            user_id:        isset($data['user_id']) ? (int) $data['user_id'] : null,
            coupon_code:    $data['coupon_code'] ?? null,
            payment_method: $data['payment_method'] ?? null,
            reward_selections: $data['reward_selections'] ?? [],
        );
    }

    /** Precomputed index: product_ids Set */
    public function productIds(): array
    {
        return array_unique(array_column(
            array_map(fn(CartItem $i) => ['product_id' => $i->product_id], $this->items),
            'product_id'
        ));
    }

    /** Precomputed index: category_ids Set */
    public function categoryIds(): array
    {
        return array_unique(array_column(
            array_map(fn(CartItem $i) => ['category_id' => $i->category_id], $this->items),
            'category_id'
        ));
    }

    /** Total quantity across all items */
    public function totalQuantity(): int
    {
        return (int) array_sum(array_map(fn(CartItem $i) => $i->quantity, $this->items));
    }

    /** Sum of items belonging to a category */
    public function subtotalByCategory(int $categoryId): float
    {
        return array_sum(array_map(
            fn(CartItem $i) => $i->category_id === $categoryId ? $i->subtotal() : 0,
            $this->items
        )) * 1000;
    }

    /** Total quantity for a specific product */
    public function quantityByProduct(int $productId): int
    {
        return (int) array_sum(array_map(
            fn(CartItem $i) => $i->product_id === $productId ? $i->quantity : 0,
            $this->items
        ));
    }

    /** Total quantity for a category */
    public function quantityByCategory(int $categoryId): int
    {
        return (int) array_sum(array_map(
            fn(CartItem $i) => $i->category_id === $categoryId ? $i->quantity : 0,
            $this->items
        ));
    }
}
