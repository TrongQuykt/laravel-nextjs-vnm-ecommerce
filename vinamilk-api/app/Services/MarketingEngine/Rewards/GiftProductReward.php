<?php

namespace App\Services\MarketingEngine\Rewards;

use App\Services\MarketingEngine\Contracts\RewardStrategy;
use App\Services\MarketingEngine\DTO\EnrichedCart;
use App\Models\Product;
use App\Models\MarketingGift;

/**
 * reward_type: gift_product
 * value: {"item_id": 99, "item_type": "product", "quantity": 2}
 *    OR  {"item_id": 1, "item_type": "gift", "quantity": 1}
 */
class GiftProductReward implements RewardStrategy
{
    public function apply(EnrichedCart $cart, array $value, string $ruleLabel = '', int $ruleId = 0): EnrichedCart
    {
        $itemId    = (int) ($value['item_id'] ?? 0);
        $itemType  = $value['item_type'] ?? 'product'; // 'product' or 'gift'
        $variantId = isset($value['variant_id']) ? (int) $value['variant_id'] : null;
        $quantity  = (int) ($value['quantity'] ?? 1);
        $volume    = $value['volume'] ?? null;
        $packing   = $value['packing'] ?? null;
        $key       = "gift_{$itemType}_{$itemId}_" . ($variantId ?? 'any');

        // Lấy thông tin dựa trên loại quà
        if ($itemType === 'gift') {
            $giftItem = MarketingGift::find($itemId);
            $name     = $giftItem?->name ?? "Quà tặng #{$itemId}";
            $image    = $giftItem?->image;
        } else {
            $product = Product::with(['images', 'volumeMedia.volume'])->find($itemId);
            $name    = $product?->name ?? "Sản phẩm #{$itemId}";
            
            // Priority 1: Gallery images
            $firstGalleryImage = $product?->images->sortBy('position')->first();
            $image = $firstGalleryImage ? $firstGalleryImage->path : null;

            // Priority 2: Product main image
            if (!$image) {
                $image = $product?->main_image;
            }

            // Priority 3: Volume Media (if product has no direct images)
            if (!$image && $product && $product->volumeMedia->isNotEmpty()) {
                $media = null;
                if ($volume) {
                    $media = $product->volumeMedia->first(function($m) use ($volume) {
                        return strtolower($m->volume?->name ?? '') === strtolower($volume);
                    });
                }
                
                if (!$media) {
                    $media = $product->volumeMedia->first();
                }

                if ($media) {
                    $image = $media->main_image;
                    if (!$image && !empty($media->images)) {
                        $image = $media->images[0]['path'] ?? null;
                    }
                }
            }
        }

        $cart->addGift($key, [
            'rule_id'    => $ruleId,
            'type'       => 'fixed',
            'item_type'  => $itemType,
            'item_id'    => $itemId,
            'variant_id' => $variantId,
            'name'       => $name,
            'image'      => $image,
            'quantity'   => $quantity,
            'volume'     => $volume,
            'packing'    => $packing,
            'price'      => 0,
            'is_gift'    => true,
            'from_rule'  => $ruleLabel,
        ]);

        return $cart;
    }
}
