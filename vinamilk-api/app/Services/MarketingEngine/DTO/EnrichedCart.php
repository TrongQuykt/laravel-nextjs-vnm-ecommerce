<?php

namespace App\Services\MarketingEngine\DTO;

class EnrichedCart
{
    /** @var CartItem[] */
    public array $items;
    public float $subtotal;
    public float $discount_total = 0;
    public float $final_total;
    public bool  $free_shipping = false;
    public int   $bonus_points = 0;

    /** Keyed by unique gift key to prevent duplicates */
    public array $gifts = [];

    /** List of discount line items */
    public array $discounts = [];

    /** Rules that were successfully applied */
    public array $applied_rules = [];

    /** User's current gift selections (rule_id => selected_id) */
    public array $reward_selections = [];

    public static function fromPayload(CartPayload $cart): self
    {
        $ec = new self();
        $ec->items             = $cart->items;
        $ec->subtotal          = $cart->subtotal;
        $ec->final_total       = $cart->subtotal;
        $ec->reward_selections = $cart->reward_selections;
        return $ec;
    }

    public function addDiscount(string $label, float $amount): void
    {
        $this->discount_total += $amount;
        $this->discounts[] = ['label' => $label, 'amount' => $amount];
    }

    public function addGift(string $key, array $gift): void
    {
        // Deduplicate: same gift key → keep higher quantity
        if (isset($this->gifts[$key])) {
            $this->gifts[$key]['quantity'] = max(
                $this->gifts[$key]['quantity'],
                $gift['quantity']
            );
        } else {
            $this->gifts[$key] = $gift;
        }
    }

    public function calculateFinalTotal(): void
    {
        // Cap discount at subtotal (never go negative)
        $this->discount_total = min($this->discount_total, $this->subtotal);
        $this->final_total    = max(0, $this->subtotal - $this->discount_total);
    }

    public function toArray(): array
    {
        return [
            'items'          => array_map(fn($i) => (array) $i, $this->items),
            'gifts'          => array_values($this->gifts),
            'discounts'      => $this->discounts,
            'subtotal'       => $this->subtotal,
            'discount_total' => $this->discount_total,
            'final_total'    => $this->final_total,
            'free_shipping'  => $this->free_shipping,
            'bonus_points'   => $this->bonus_points,
            'savings'        => $this->discount_total,
            'applied_rules'  => $this->applied_rules,
        ];
    }
}
