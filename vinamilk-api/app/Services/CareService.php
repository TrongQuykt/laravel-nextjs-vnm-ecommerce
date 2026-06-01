<?php

namespace App\Services;

use App\Models\CareDeliveryOption;
use App\Models\CareProduct;
use App\Models\Product;
use App\Models\ProductVariant;
use Carbon\Carbon;

class CareService
{
    public static function normalizePrice(float $raw): int
    {
        return ($raw > 0 && $raw < 10000) ? (int) round($raw * 1000) : (int) round($raw);
    }

    public function resolveVariant(CareProduct $careProduct, int $variantId): ProductVariant
    {
        $variant = ProductVariant::with(['product', 'volume', 'packagingType'])
            ->where('id', $variantId)
            ->where('product_id', $careProduct->product_id)
            ->where('is_active', true)
            ->first();

        if (!$variant) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'variant_id' => ['Biến thể không thuộc sản phẩm Care đã chọn.'],
            ]);
        }

        return $variant;
    }

    public function unitPriceFromVariant(ProductVariant $variant): int
    {
        return self::normalizePrice((float) $variant->price);
    }

    public function calculate(array $params): array
    {
        $careProduct = CareProduct::with('product.category')
            ->where('is_active', true)
            ->findOrFail($params['care_product_id']);

        $variant = $this->resolveVariant($careProduct, (int) $params['variant_id']);
        $qty = max(1, (int) ($params['quantity'] ?? 1));

        $deliveryOption = CareDeliveryOption::where('is_active', true)
            ->where('delivery_count', $params['delivery_count'])
            ->firstOrFail();

        $unitPrice = $this->unitPriceFromVariant($variant);
        $deliveryCount = (int) $deliveryOption->delivery_count;
        $packageSubtotal = $unitPrice * $qty * $deliveryCount;
        $total = $packageSubtotal;

        $firstDate = isset($params['first_delivery_date'])
            ? Carbon::parse($params['first_delivery_date'])
            : Carbon::today()->addDays(7);

        $schedule = [];
        for ($i = 0; $i < $deliveryCount; $i++) {
            $schedule[] = $firstDate->copy()->addMonths($i)->toDateString();
        }

        return [
            'care_product_id'   => $careProduct->id,
            'product_id'        => $careProduct->product_id,
            'variant_id'        => $variant->id,
            'quantity'          => $qty,
            'delivery_count'    => $deliveryCount,
            'discount_percent'  => 0,
            'unit_price'        => $unitPrice,
            'package_subtotal'  => $packageSubtotal,
            'discount_amount'   => 0,
            'total_amount'      => $total,
            'delivery_schedule' => $schedule,
        ];
    }

    public function formatCareProduct(CareProduct $cp): ?array
    {
        $product = $cp->product;
        if (!$product) {
            return null;
        }

        $variants = ProductVariant::with(['volume', 'packagingType'])
            ->where('product_id', $product->id)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        if ($variants->isEmpty()) {
            return null;
        }

        $prices = $variants->map(fn ($v) => self::normalizePrice((float) $v->price));
        $basePrices = $variants->map(fn ($v) => self::normalizePrice((float) ($v->base_price ?: $v->price)));
        $fromPrice = $prices->min();
        $fromBase = $basePrices->max();
        $discountPct = $fromBase > $fromPrice
            ? (int) round((1 - $fromPrice / $fromBase) * 100)
            : 0;

        $imagePath = null;
        if ($product->homeFeaturedVolume?->main_image) {
            $imagePath = $product->homeFeaturedVolume->main_image;
        } elseif ($product->relationLoaded('volumeMedia') && $product->volumeMedia->isNotEmpty()) {
            $imagePath = $product->volumeMedia->first()->main_image;
        } elseif ($product->main_image) {
            $imagePath = $product->main_image;
        }

        return [
            'id'                => $cp->id,
            'product_id'        => $product->id,
            'slug'              => $product->slug,
            'name'              => $product->name,
            'category_name'     => $product->category?->name,
            'short_description' => $product->short_description,
            'image'             => $imagePath ? asset('storage/' . $imagePath) : null,
            'base_price'        => $fromBase,
            'care_price'        => $fromPrice,
            'discount_percent'  => $discountPct,
        ];
    }
}
