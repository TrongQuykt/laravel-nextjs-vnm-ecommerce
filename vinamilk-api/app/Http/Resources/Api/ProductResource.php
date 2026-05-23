<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'ingredients' => $this->ingredients,
            'usage_instructions' => $this->usage_instructions,
            'storage_instructions' => $this->storage_instructions,
            'nutrition_facts' => $this->nutrition_facts,
            
            // Relationships
            'category' => new CategoryResource($this->whenLoaded('category')),
            'product_line' => ($this->whenLoaded('productLine') && $this->productLine) ? [
                'id' => $this->productLine->id,
                'name' => $this->productLine->name,
                'slug' => $this->productLine->slug,
            ] : null,
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'sugar_level' => new AttributeResource($this->whenLoaded('sugarLevel')),
            'nutritional_needs' => AttributeResource::collection($this->whenLoaded('nutritionalNeeds')),
            
            // Media
            'home_featured_volume_id' => $this->home_featured_volume_id,
            'home_featured_variant' => (function() {
                $hfv = $this->homeFeaturedVolume;
                if (!$hfv) return null;
                
                $variant = $this->variants->where('volume_id', $hfv->volume_id)->first();
                if (!$variant) return null;

                $gallery = is_array($hfv->images) 
                    ? collect($hfv->images)
                        ->sortBy('position')
                        ->map(fn($img) => asset('storage/' . (is_array($img) ? ($img['path'] ?? '') : $img)))
                        ->values()
                        ->all()
                    : [];

                return [
                    'id' => $variant->id,
                    'flavor' => $variant->flavor ? $variant->flavor->name : null,
                    'flavor_slug' => $variant->flavor ? $variant->flavor->slug : null,
                    'price' => (float) $variant->price,
                    'base_price' => (float) $variant->base_price,
                    'discount_percentage' => (int) $variant->discount_percentage,
                    'volume' => $variant->volume ? $variant->volume->name : null,
                    'volume_slug' => $variant->volume ? $variant->volume->slug : null,
                    'packaging_type' => $variant->packagingType ? $variant->packagingType->name : null,
                    'packaging_type_slug' => $variant->packaging_type_slug,
                    'main_image' => $hfv->main_image ? asset('storage/' . $hfv->main_image) : null,
                    'images' => $gallery,
                ];
            })(),
            'main_image' => (function() {
                if ($this->homeFeaturedVolume) {
                    return $this->homeFeaturedVolume->main_image ? asset('storage/' . $this->homeFeaturedVolume->main_image) : null;
                }
                $firstVm = $this->volumeMedia->first();
                return $firstVm && $firstVm->main_image ? asset('storage/' . $firstVm->main_image) : null;
            })(),
            'images' => [], // Gallery is now tied to volumes, returned per variant
            
            // Variants
            'variants' => $this->variants->sortBy('position')->values()->map(function ($v) {
                $vm = $this->volumeMedia->where('volume_id', $v->volume_id)->first();
                $gallery = is_array($vm?->images) 
                    ? collect($vm->images)
                        ->sortBy('position')
                        ->map(fn($img) => asset('storage/' . (is_array($img) ? ($img['path'] ?? '') : $img)))
                        ->values()
                        ->all()
                    : [];

                return [
                    'id' => $v->id,
                    'sku' => $v->sku,
                    'name' => $v->name,
                    'base_price' => (float) $v->base_price,
                    'price' => (float) $v->price,
                    'discount_percentage' => (int) $v->discount_percentage,
                    'stock_quantity' => $v->stock_quantity,
                    'is_active' => $v->is_active,
                    'position' => (int) $v->position,
                    'flavor' => $v->flavor ? $v->flavor->name : null,
                    'flavor_slug' => $v->flavor ? $v->flavor->slug : null,
                    'volume' => $v->volume ? $v->volume->name : null,
                    'volume_slug' => $v->volume ? $v->volume->slug : null,
                    'packaging_type' => $v->packagingType ? $v->packagingType->name : null,
                    'packaging_type_slug' => $v->packaging_type_slug,
                    'main_image' => $vm && $vm->main_image ? asset('storage/' . $vm->main_image) : null,
                    'images' => $gallery,
                ];
            }),
            
            'created_at' => $this->created_at?->toIso8601String(),

            // Custom Layout Fields
            'features_title' => $this->features_title,
            'description_title' => $this->description_title,
            'description_image' => $this->description_image ? asset('storage/' . $this->description_image) : null,
            'comparison_title' => $this->comparison_title,
            'features_main_image' => $this->features_main_image ? asset('storage/' . $this->features_main_image) : null,
            'features' => $this->features,
            'comparison_table_headers' => $this->comparison_table_headers,
            'comparison_table_rows' => $this->comparison_table_rows,
            'special_highlights' => $this->specialHighlights->map(fn ($h) => [
                'id' => $h->id,
                'name' => $h->name,
                'icon' => $h->icon ? asset('storage/' . $h->icon) : null,
            ]),
            'certificates' => $this->certificates->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'icon' => $c->icon ? asset('storage/' . $c->icon) : null,
            ]),
            'card_tag' => $this->cardTag ? [
                'id' => $this->cardTag->id,
                'name' => $this->cardTag->name,
                'icon' => $this->cardTag->icon ? asset('storage/' . $this->cardTag->icon) : null,
            ] : null,
        ];
    }
}
