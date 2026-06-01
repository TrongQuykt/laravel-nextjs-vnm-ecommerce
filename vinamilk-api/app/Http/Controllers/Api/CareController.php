<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CareDelivery;
use App\Models\CareDeliveryOption;
use App\Models\CareGreetingCard;
use App\Models\CarePageSetting;
use App\Models\CareProduct;
use App\Models\CareSubscription;
use App\Models\MarketingGift;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Services\CareService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CareController extends Controller
{
    public function __construct(private CareService $careService) {}

    public function page()
    {
        $settings = CarePageSetting::first();
        $deliveryOptions = CareDeliveryOption::where('is_active', true)->orderBy('sort_order')->get();

        if ($settings?->hero_image_path) {
            $settings->hero_image_path = asset('storage/' . $settings->hero_image_path);
        }

        return response()->json([
            'settings'         => $settings,
            'delivery_options' => $deliveryOptions,
        ]);
    }

    public function products()
  {
        $products = CareProduct::with(['product.category', 'product.volumeMedia', 'product.homeFeaturedVolume'])
            ->where('is_active', true)
            ->orderBy('id')
            ->get()
            ->map(fn ($cp) => $this->careService->formatCareProduct($cp))
            ->filter()
            ->values();

        return response()->json(['products' => $products]);
    }

    public function greetingCards()
    {
        $cards = CareGreetingCard::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($card) {
                if ($card->preview_image_path) {
                    $card->preview_image_path = asset('storage/' . $card->preview_image_path);
                }
                return $card;
            });

        return response()->json(['cards' => $cards]);
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'care_product_id'     => 'required|exists:care_products,id',
            'variant_id'          => 'required|exists:product_variants,id',
            'quantity'            => 'required|integer|min:1|max:99',
            'delivery_count'      => 'required|integer|in:3,6,9',
            'first_delivery_date' => 'nullable|date|after_or_equal:today',
        ]);

        return response()->json($this->careService->calculate($request->all()));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'care_product_id'       => 'required|exists:care_products,id',
            'variant_id'            => 'required|exists:product_variants,id',
            'quantity'              => 'required|integer|min:1|max:99',
            'delivery_count'        => 'required|integer|in:3,6,9',
            'include_greeting_card' => 'boolean',
            'greeting_card_id'      => 'nullable|exists:care_greeting_cards,id',
            'first_delivery_date'   => 'required|date|after_or_equal:today',
            'shipping_address'      => 'required|array',
            'payment_method'        => 'required|in:momo,vnpay,stripe,paypal',
            'invoice_info'          => 'nullable|array',
            'notes'                 => 'nullable|string',
            'selected_gifts'        => 'nullable|array',
            'selected_gifts.*'      => 'array',
        ]);

        if ($request->boolean('include_greeting_card') && !$request->greeting_card_id) {
            return response()->json(['message' => 'Vui lòng chọn lời nhắn thiệp hoặc tắt thiệp đính kèm.'], 422);
        }

        return DB::transaction(function () use ($request) {
            $pricing = $this->careService->calculate($request->all());

            // If frontend supplied pricing fields, prefer them but log mismatch
            $frontendPricing = $request->input('pricing');
            $frontendPackageSubtotal = $request->input('package_subtotal');
            $frontendDiscountAmount = $request->input('discount_amount');
            $frontendTotalAmount = $request->input('total_amount');

            if ($frontendPackageSubtotal !== null || $frontendTotalAmount !== null) {
                // Use frontend values where provided, fallback to server calculation
                $pricing['package_subtotal'] = $frontendPackageSubtotal !== null ? (float) $frontendPackageSubtotal : $pricing['package_subtotal'];
                $pricing['discount_amount'] = $frontendDiscountAmount !== null ? (float) $frontendDiscountAmount : ($pricing['discount_amount'] ?? 0);
                $pricing['total_amount'] = $frontendTotalAmount !== null ? (float) $frontendTotalAmount : $pricing['total_amount'];

                if ($frontendPricing && isset($frontendPricing['unit_price'])) {
                    $pricing['unit_price'] = (float) $frontendPricing['unit_price'];
                }

                // Log if there's a difference between server calc and frontend-supplied numbers
                try {
                    $serverCalc = $this->careService->calculate($request->all());
                    if (abs(($serverCalc['total_amount'] ?? 0) - ($pricing['total_amount'] ?? 0)) > 0.5) {
                        \Log::warning('Frontend pricing differs from server calculation for care checkout', [
                            'server' => $serverCalc,
                            'frontend' => ['package_subtotal' => $frontendPackageSubtotal, 'discount_amount' => $frontendDiscountAmount, 'total_amount' => $frontendTotalAmount],
                            'user_id' => auth()->id(),
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::debug('Could not re-calc server pricing for comparison: ' . $e->getMessage());
                }
            }

            $selectedGifts = $request->input('selected_gifts', []);
            $giftVariantId = null;
            foreach (array_values($selectedGifts) as $giftPayload) {
                if (!is_array($giftPayload)) {
                    continue;
                }
                $giftVariantIdValue = isset($giftPayload['product_variant_id'])
                    ? (int) $giftPayload['product_variant_id']
                    : (isset($giftPayload['variant_id']) ? (int) $giftPayload['variant_id'] : null);
                if ($giftVariantIdValue && !$giftVariantId) {
                    $giftVariantId = $giftVariantIdValue;
                }
            }

            $careProduct = CareProduct::findOrFail($request->care_product_id);
            $variant = $this->careService->resolveVariant($careProduct, (int) $request->variant_id);
            $variant->load(['product', 'volume', 'packagingType']);
            $deliveryCount = (int) $request->delivery_count;
            $qty = (int) $request->quantity;
            $totalQty = $qty * $deliveryCount;

            if ($variant->stock_quantity < $totalQty) {
                return response()->json(['message' => 'Sản phẩm không đủ tồn kho cho gói Care.'], 422);
            }

            $subscription = CareSubscription::create([
                'user_id'               => auth()->id(),
                'tier'                  => 'standard',
                'delivery_count'        => $deliveryCount,
                'product_variant_id'    => $variant->id,
                'gift_variant_id'       => $giftVariantId,
                'quantity_per_delivery' => $qty,
                'include_greeting_card' => $request->boolean('include_greeting_card'),
                'greeting_card_id'      => $request->boolean('include_greeting_card') ? $request->greeting_card_id : null,
                'unit_price'            => $pricing['unit_price'],
                'package_subtotal'      => $pricing['package_subtotal'],
                'discount_amount'       => $pricing['discount_amount'] ?? 0,
                'discount_percent'      => $pricing['discount_percent'] ?? 0,
                'total_amount'          => $pricing['total_amount'],
                'first_delivery_date'   => $request->first_delivery_date,
                'shipping_address'      => $request->shipping_address,
                'status'                => 'pending_payment',
            ]);

            foreach ($pricing['delivery_schedule'] as $index => $date) {
                CareDelivery::create([
                    'care_subscription_id'   => $subscription->id,
                    'delivery_index'         => $index + 1,
                    'scheduled_date'         => $date,
                    'includes_gift'          => $index === 0 && !empty($selectedGifts),
                    'includes_greeting_card' => $index === 0 && $request->boolean('include_greeting_card'),
                    'status'                 => 'scheduled',
                ]);
            }

            $orderNumber = 'CARE-' . date('ymdHis') . strtoupper(Str::random(4));
            $shipMethod = ShippingMethod::where('provider', 'standard')->first()
                ?? ShippingMethod::first();

            $order = Order::create([
                'user_id'                => auth()->id(),
                'order_number'           => $orderNumber,
                'order_type'             => 'care',
                'care_subscription_id'   => $subscription->id,
                'status'                 => 'pending',
                'delivery_type'          => 'shipping',
                'total_amount'           => $pricing['total_amount'],
                'discount_amount'        => $pricing['discount_amount'] ?? 0,
                'shipping_cost'          => 0,
                'payment_method'         => $request->payment_method,
                'payment_status'         => 'pending_payment',
                'shipping_address'       => $request->shipping_address,
                'shipping_method_id'     => $shipMethod?->id,
                'shipping_method_name'   => $shipMethod?->name ?? 'Giao hàng tiêu chuẩn',
                'expected_delivery_date' => $request->first_delivery_date,
                'invoice_info'           => $request->invoice_info,
                'notes'                  => ($request->notes ? $request->notes . "\n" : '')
                    . "[Vinamilk Care] Gói {$deliveryCount} lần × {$qty} SP/kỳ — Subscription #{$subscription->id}",
            ]);

            $subscription->update(['payment_order_id' => $order->id]);

            $volumeMedia = $variant->product->volumeMedia()->where('volume_id', $variant->volume_id)->first();
            $image = $variant->main_image ?? ($volumeMedia?->main_image ?? $variant->product->main_image);
            $unitPrice = $pricing['unit_price'];
            $basePrice = CareService::normalizePrice((float) ($variant->base_price ?: $variant->price));

            // Create one order item per scheduled delivery (package)
            foreach ($pricing['delivery_schedule'] as $index => $date) {
                $pkgNum = "{$orderNumber}FN" . ($index + 1);
                $order->items()->create([
                    'product_variant_id' => $variant->id,
                    'product_name'       => $variant->product->name,
                    'variant_name'       => $variant->name . " (Care delivery " . ($index + 1) . ")",
                    'image'              => $image,
                    'volume'             => $variant->volume?->name,
                    'packing_type'       => $variant->packagingType?->name,
                    'original_price'     => $basePrice * $qty,
                    'quantity'           => $qty,
                    'price'              => $unitPrice,
                    'total'              => $unitPrice * $qty,
                    'package_number'     => $pkgNum,
                ]);
            }

            $giftItems = [];
            foreach (array_values($selectedGifts) as $giftPayload) {
                if (!is_array($giftPayload)) {
                    continue;
                }

                $giftQuantity = max(1, (int) ($giftPayload['quantity'] ?? 1));
                $giftVariantIdValue = isset($giftPayload['product_variant_id'])
                    ? (int) $giftPayload['product_variant_id']
                    : (isset($giftPayload['variant_id']) ? (int) $giftPayload['variant_id'] : null);
                $marketingGiftIdValue = isset($giftPayload['marketing_gift_id'])
                    ? (int) $giftPayload['marketing_gift_id']
                    : (isset($giftPayload['gift_id']) ? (int) $giftPayload['gift_id'] : null);
                $itemType = strtolower((string) ($giftPayload['item_type'] ?? $giftPayload['type'] ?? ''));

                if (!$giftVariantIdValue && !$marketingGiftIdValue && isset($giftPayload['id'])) {
                    if (str_contains($itemType, 'gift') || empty($giftPayload['name'])) {
                        $marketingGiftIdValue = (int) $giftPayload['id'];
                    } else {
                        $giftVariantIdValue = (int) $giftPayload['id'];
                    }
                }

                $itemName = $giftPayload['name'] ?? $giftPayload['title'] ?? 'Quà tặng Care';
                $variantName = $giftPayload['variant_name'] ?? $giftPayload['subtitle'] ?? 'Quà tặng';
                $imageUrl = $giftPayload['image'] ?? null;

                $orderItem = [
                    'product_variant_id' => null,
                    'marketing_gift_id'  => null,
                    'product_name'       => $itemName,
                    'variant_name'       => $variantName,
                    'image'              => $imageUrl,
                    'volume'             => null,
                    'packing_type'       => null,
                    'original_price'     => 0,
                    'quantity'           => $giftQuantity,
                    'price'              => 0,
                    'total'              => 0,
                    'package_number'     => "{$orderNumber}SXU",
                ];

                if ($giftVariantIdValue) {
                    $giftVariant = ProductVariant::with('product')->find($giftVariantIdValue);
                    if (!$giftVariant) {
                        continue;
                    }
                    $orderItem['product_variant_id'] = $giftVariant->id;
                    $orderItem['product_name'] = $giftVariant->product->name;
                    $orderItem['variant_name'] = $giftVariant->name;
                    $orderItem['image'] = $giftVariant->main_image ?? $giftVariant->product->main_image;
                } elseif ($marketingGiftIdValue) {
                    $marketingGift = MarketingGift::find($marketingGiftIdValue);
                    if (!$marketingGift) {
                        continue;
                    }
                    $orderItem['marketing_gift_id'] = $marketingGift->id;
                    $orderItem['product_name'] = $marketingGift->name;
                    $orderItem['variant_name'] = $variantName;
                    $orderItem['image'] = $marketingGift->image ? asset('storage/' . $marketingGift->image) : $orderItem['image'];
                }

                $giftItems[] = $orderItem;
            }

            if (!empty($giftItems)) {
                $order->items()->createMany($giftItems);
            }

            if ($request->boolean('include_greeting_card')) {
                $greetingCard = CareGreetingCard::find($request->greeting_card_id);
                $cardTitle = $greetingCard?->title ? "Thiệp: {$greetingCard->title}" : 'Thiệp chúc mừng';
                $cardPreview = $greetingCard?->preview_image_path ? asset('storage/' . $greetingCard->preview_image_path) : null;

                $order->items()->create([
                    'product_variant_id' => null,
                    'marketing_gift_id'  => null,
                    'product_name'       => 'Thiệp chúc mừng',
                    'variant_name'       => $cardTitle,
                    'image'              => $cardPreview,
                    'volume'             => null,
                    'packing_type'       => null,
                    'original_price'     => 0,
                    'quantity'           => 1,
                    'price'              => 0,
                    'total'              => 0,
                    'package_number'     => "{$orderNumber}GIFT",
                ]);
            }

            // Decrement stock once for the whole subscription
            $variant->decrement('stock_quantity', $totalQty);

            $paymentUrl = null;
            try {
                $paymentUrl = (new \App\Services\PaymentService())->createPaymentUrl($order);
            } catch (\Exception $e) {
                \Log::error("Care payment URL error: " . $e->getMessage());
            }

            \App\Models\PaymentLog::create([
                'order_number'    => $order->order_number,
                'payment_method'  => $order->payment_method,
                'amount'          => $order->total_amount,
                'status'          => 'pending',
                'request_payload' => $request->except(['shipping_address']),
                'tenant_id'       => $order->tenant_id,
            ]);

            return response()->json([
                'success'              => true,
                'order_number'         => $order->order_number,
                'care_subscription_id' => $subscription->id,
                'payment_url'          => $paymentUrl,
                'pricing'              => $pricing,
            ]);
        });
    }
}
