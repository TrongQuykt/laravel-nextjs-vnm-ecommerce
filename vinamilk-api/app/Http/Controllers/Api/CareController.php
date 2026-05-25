<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CareDelivery;
use App\Models\CareDeliveryOption;
use App\Models\CareGreetingCard;
use App\Models\CarePageSetting;
use App\Models\CareProduct;
use App\Models\CareSubscription;
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
        ]);

        if ($request->boolean('include_greeting_card') && !$request->greeting_card_id) {
            return response()->json(['message' => 'Vui lòng chọn lời nhắn thiệp hoặc tắt thiệp đính kèm.'], 422);
        }

        return DB::transaction(function () use ($request) {
            $pricing = $this->careService->calculate($request->all());

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
                'gift_variant_id'       => null,
                'quantity_per_delivery' => $qty,
                'include_greeting_card' => $request->boolean('include_greeting_card'),
                'greeting_card_id'      => $request->boolean('include_greeting_card') ? $request->greeting_card_id : null,
                'unit_price'            => $pricing['unit_price'],
                'package_subtotal'      => $pricing['package_subtotal'],
                'discount_amount'       => 0,
                'discount_percent'      => 0,
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
                    'includes_gift'          => false,
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
                'discount_amount'        => 0,
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

            $order->items()->create([
                'product_variant_id' => $variant->id,
                'product_name'       => $variant->product->name,
                'variant_name'       => $variant->name . " (Care {$deliveryCount} kỳ)",
                'image'              => $image,
                'volume'             => $variant->volume?->name,
                'packing_type'       => $variant->packagingType?->name,
                'original_price'     => $basePrice * $totalQty,
                'quantity'           => $totalQty,
                'price'              => $unitPrice,
                'total'              => $pricing['package_subtotal'],
                'package_number'     => "{$orderNumber}FN1",
            ]);

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
