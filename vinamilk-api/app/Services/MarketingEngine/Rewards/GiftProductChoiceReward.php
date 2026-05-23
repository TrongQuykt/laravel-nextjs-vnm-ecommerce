<?php

namespace App\Services\MarketingEngine\Rewards;

use App\Services\MarketingEngine\Contracts\RewardStrategy;
use App\Services\MarketingEngine\DTO\EnrichedCart;
use App\Models\Product;
use App\Models\MarketingGift;

/**
 * reward_type: gift_product_choice
 * value: {
 *   "items": [{"id": 10, "type": "product"}, {"id": 1, "type": "gift"}], 
 *   "pick": 1, 
 *   "quantity": 1
 * }
 */
class GiftProductChoiceReward implements RewardStrategy
{
    public function apply(EnrichedCart $cart, array $value, string $ruleLabel = '', int $ruleId = 0): EnrichedCart
    {
        $items = (array) ($value['items'] ?? []);
        $key   = 'gift_choice_' . md5(json_encode($items));

        if (!isset($cart->gifts[$key])) {
            $options = [];
            foreach ($items as $item) {
                $id       = (int) $item['id'];
                $type     = $item['type'] ?? 'product';
                $qty      = (int) ($item['quantity'] ?? 1);
                $vol      = $item['volume'] ?? null;
                $pack     = $item['packing'] ?? null;

                if ($type === 'gift') {
                    $gift = MarketingGift::find($id);
                    if ($gift) {
                        $image = $gift->image;
                        $options[] = [
                            'id'        => $id,
                            'item_id'   => $id,
                            'item_type' => 'gift',
                            'name'      => $gift->name,
                            'image'     => $image,
                            'quantity'  => $qty,
                            'volume'    => $vol,
                            'packing'   => $pack,
                        ];
                    }
                } else {
                    $product = Product::with(['images', 'volumeMedia.volume'])->find($id);
                    if ($product) {
                        // Priority 1: Gallery images
                        $firstGalleryImage = $product->images->sortBy('position')->first();
                        $image = $firstGalleryImage ? $firstGalleryImage->path : null;

                        // Priority 2: Product main image
                        if (!$image) {
                            $image = $product->main_image;
                        }

                        // Priority 3: Volume Media (if product has no direct images)
                        if (!$image && $product->volumeMedia->isNotEmpty()) {
                            $media = null;
                            if ($vol) {
                                $media = $product->volumeMedia->first(function($m) use ($vol) {
                                    return strtolower($m->volume?->name ?? '') === strtolower($vol);
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

                        $options[] = [
                            'id'        => $id,
                            'item_id'   => $id,
                            'item_type' => 'product',
                            'name'      => $product->name,
                            'image'     => $image,
                            'quantity'  => $qty,
                            'volume'    => $vol,
                            'packing'   => $pack,
                        ];
                    }
                }
            }

            $selections = $cart->reward_selections[$ruleId] ?? [];
            $selectedId = is_array($selections) ? (int) ($selections[0] ?? 0) : (int) $selections;
            
            $selectedOption = collect($options)->firstWhere('id', $selectedId);

            $cart->gifts[$key] = [
                'rule_id'     => $ruleId,
                'type'        => 'choice',
                'options'     => $options,
                'selected_id' => $selectedId,
                'selected_option' => $selectedOption,
                'pick_count'  => (int) ($value['pick_count'] ?? 1),
                'price'       => 0,
                'is_gift'     => true,
                'from_rule'   => $ruleLabel,
            ];
        }

        return $cart;
    }
}
