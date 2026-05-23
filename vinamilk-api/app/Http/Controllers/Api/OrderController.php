<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Handle Checkout process with sandbox simulations.
     */
    public function checkout(Request $request)
    {
        // Tự động chuyển đổi product_id thành variant_id đầu tiên nếu cần (dành cho quà tặng)
        $items = $request->items;
        if (is_array($items)) {
            foreach ($items as $index => $item) {
                if (isset($item['product_id']) && !is_null($item['product_id'])) {
                    $firstVariant = \App\Models\ProductVariant::where('product_id', $item['product_id'])->first();
                    if ($firstVariant) {
                        $items[$index]['variant_id'] = $firstVariant->id;
                    }
                } elseif (isset($item['variant_id']) && !is_null($item['variant_id'])) {
                    // Nếu variant_id không tồn tại, kiểm tra xem nó có phải là product_id không
                    if (!\App\Models\ProductVariant::where('id', $item['variant_id'])->exists()) {
                        $firstVariant = \App\Models\ProductVariant::where('product_id', $item['variant_id'])->first();
                        if ($firstVariant) {
                            $items[$index]['variant_id'] = $firstVariant->id;
                        }
                    }
                }
            }
            $request->merge(['items' => $items]);
        }

        try {
            $request->validate([
                'items' => 'required|array',
                'items.*.product_id' => 'nullable|integer',
                'items.*.variant_id' => 'required_without_all:items.*.marketing_gift_id,items.*.product_id|nullable|exists:product_variants,id',
                'items.*.marketing_gift_id' => 'required_without_all:items.*.variant_id,items.*.product_id|nullable|exists:marketing_gifts,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.is_gift' => 'nullable|boolean',
                'payment_method' => 'required|in:cod,momo,vnpay,stripe,paypal',
                'delivery_type' => 'required|in:shipping,pickup',
                
                // Shipping specific
                'shipping_address' => 'required_if:delivery_type,shipping|array',
                'shipping_method_id' => 'required_if:delivery_type,shipping',
                'shipping_cost' => 'required|numeric',
                
                // Pickup specific
                'store_id' => 'required_if:delivery_type,pickup|exists:stores,id',
                'pickup_time' => 'required_if:delivery_type,pickup|string',
                'receiver_name' => 'required_if:delivery_type,pickup|string',
                'receiver_phone' => 'required_if:delivery_type,pickup|string',

                // Common
                'discount_amount' => 'nullable|numeric',
                'voucher_code' => 'nullable|string',
                'invoice_info' => 'nullable|array',
                'expected_delivery_date' => 'nullable|date',
                'notes' => 'nullable|string',
                'applied_redemption_ids' => 'nullable|array',
                'applied_redemption_ids.*' => 'integer|exists:reward_redemptions,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error("Checkout Validation Failed: ", [
                'errors' => $e->errors(),
                'input' => $request->only(['items', 'delivery_type', 'payment_method'])
            ]);
            throw $e;
        }

        return DB::transaction(function () use ($request) {
            $subtotal = 0;
            $orderItemsData = [];

            $appliedRedemptions = [];
            $personalVoucherDiscount = 0;
            
            if ($request->has('applied_redemption_ids') && is_array($request->applied_redemption_ids)) {
                $userId = auth()->id();
                $redemptions = \App\Models\RewardRedemption::with('reward')
                    ->where('user_id', $userId)
                    ->whereIn('id', $request->applied_redemption_ids)
                    ->where('status', 'completed')
                    ->whereNull('order_id')
                    ->get();
                    
                foreach ($redemptions as $redemption) {
                    $appliedRedemptions[] = $redemption;
                    if ($redemption->reward && $redemption->reward->type === 'voucher') {
                        preg_match('/(\d+)K.*?(\d+)K/i', $redemption->reward->name, $matches);
                        if ($matches) {
                            $personalVoucherDiscount += (int)$matches[1] * 1000;
                        }
                    }
                }
            }

            foreach ($request->items as $item) {
                if (!empty($item['marketing_gift_id'])) {
                    $gift = \App\Models\MarketingGift::find($item['marketing_gift_id']);
                    if (!$gift) continue;

                    $orderItemsData[] = [
                        'product_variant_id' => null,
                        'marketing_gift_id' => $gift->id,
                        'product_name' => $gift->name,
                        'variant_name' => 'Quà tặng vật phẩm',
                        'image' => $gift->image,
                        'volume' => null,
                        'packing_type' => null,
                        'original_price' => 0,
                        'quantity' => $item['quantity'],
                        'price' => 0,
                        'total' => 0,
                    ];
                    continue;
                }

                $variant = ProductVariant::with(['product', 'volume', 'packagingType'])->find($item['variant_id']);
                if (!$variant || !$variant->product) continue;

                $rawPrice = (float)$variant->price;
                $rawBasePrice = (float)$variant->base_price;

                // Giá DB lưu đơn vị nghìn đồng (ví dụ 295.99 -> 295,990đ)
                // Nếu là quà tặng thì giá = 0
                $isGift = ($rawPrice == 0) || (isset($item['is_gift']) && $item['is_gift'] == true);
                
                $purchasePrice = $isGift ? 0 : (($rawPrice > 0 && $rawPrice < 10000) ? round($rawPrice * 1000) : round($rawPrice));
                $originalPrice = ($rawBasePrice > 0 && $rawBasePrice < 10000) ? round($rawBasePrice * 1000) : round($rawBasePrice);
                
                $itemTotal = $purchasePrice * $item['quantity'];
                $subtotal += $itemTotal;

                $volumeMedia = $variant->product->volumeMedia()->where('volume_id', $variant->volume_id)->first();
                $image = $variant->main_image ?? ($volumeMedia ? $volumeMedia->main_image : $variant->product->main_image);

                $orderItemsData[] = [
                    'product_variant_id' => $variant->id,
                    'marketing_gift_id' => null,
                    'product_name' => $variant->product->name,
                    'variant_name' => $variant->name,
                    'image' => $image,
                    'volume' => $variant->volume->name ?? null,
                    'packing_type' => $variant->packagingType->name ?? null,
                    'original_price' => $originalPrice,
                    'quantity' => $item['quantity'],
                    'price' => $purchasePrice,
                    'total' => $itemTotal,
                ];

                // Trừ tồn kho (chỉ cho sản phẩm tính tiền)
                if ($purchasePrice > 0) {
                    $variant->decrement('stock_quantity', $item['quantity']);
                }
            }

            // Thêm các quà tặng vật lý quy đổi cá nhân vào danh sách mặt hàng của đơn hàng
            foreach ($appliedRedemptions as $redemption) {
                if ($redemption->reward && $redemption->reward->type === 'gift') {
                    $orderItemsData[] = [
                        'product_variant_id' => null,
                        'marketing_gift_id' => null,
                        'product_name' => '[QUÀ QUY ĐỔI] ' . $redemption->reward->name,
                        'variant_name' => 'Quà quy đổi điểm',
                        'image' => $redemption->reward->image,
                        'volume' => null,
                        'packing_type' => null,
                        'original_price' => 0,
                        'quantity' => 1,
                        'price' => 0,
                        'total' => 0,
                    ];
                }
            }

            $discount = $request->discount_amount ?? 0;
            if ($personalVoucherDiscount > 0) {
                $discount = max($discount, $personalVoucherDiscount);
            }
            $shippingCost = $request->shipping_cost ?? 0;

            if ($request->delivery_type === 'shipping' && $shippingCost == 0) {
                $ghn = new \App\Services\GHNService();
                $toDistrictId = $request->shipping_address['ghn_district_id'] ?? 1454;
                $toWardCode = $request->shipping_address['ghn_ward_code'] ?? "21012";
                $shippingCost = $ghn->calculateFee($toDistrictId, $toWardCode);
            }

            $totalAmount = $subtotal - $discount + $shippingCost;

            $orderNumber = 'ES-' . date('ymdHis') . strtoupper(Str::random(4));
            $orderData = [
                'user_id' => auth()->id(),
                'order_number' => $orderNumber,
                'status' => 'pending',
                'delivery_type' => $request->delivery_type,
                'total_amount' => $totalAmount,
                'discount_amount' => $discount,
                'voucher_code' => $request->voucher_code,
                'shipping_cost' => $shippingCost,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'invoice_info' => $request->invoice_info,
                'expected_delivery_date' => $request->expected_delivery_date,
                'payment_status' => $request->payment_method === 'cod' ? 'unpaid' : 'pending_payment',
            ];

            if ($request->delivery_type === 'shipping') {
                $orderData['shipping_address'] = $request->shipping_address;
                
                $shipMethod = \App\Models\ShippingMethod::where('id', $request->shipping_method_id)
                    ->orWhere('provider', $request->shipping_method_id)
                    ->first();
                
                if ($shipMethod) {
                    $orderData['shipping_method_id'] = $shipMethod->id;
                    $orderData['shipping_method_name'] = $shipMethod->name;
                } else {
                    $orderData['shipping_method_id'] = 1;
                    $orderData['shipping_method_name'] = "Giao hàng tiêu chuẩn";
                }
            } else {
                $orderData['store_id'] = $request->store_id;
                $orderData['pickup_time'] = $request->pickup_time;
                $orderData['shipping_address'] = [
                    'name' => $request->receiver_name,
                    'phone' => $request->receiver_phone,
                    'type' => 'pickup'
                ];
                $orderData['shipping_method_name'] = "Nhận tại cửa hàng";
            }

            $order = Order::create($orderData);
            
            // Cập nhật order_id cho các voucher/quà tặng cá nhân đã áp dụng để không thể tái sử dụng
            foreach ($appliedRedemptions as $redemption) {
                $redemption->update([
                    'order_id' => $order->id
                ]);
            }
            
            // Kiện hàng: FN1/FN2 (Sản phẩm chính), SXU (Quà tặng)
            $pkgStandard = "{$orderNumber}FN1";
            $pkgGift = "{$orderNumber}SXU";

            foreach ($orderItemsData as &$item) {
                $item['package_number'] = $item['price'] > 0 ? $pkgStandard : $pkgGift;
            }
            $order->items()->createMany($orderItemsData);

            // Tích điểm 3 tầng theo quy định Vinamilk Rewards
            $totalPointsEarned = 0;
            foreach ($request->items as $item) {
                $variant = ProductVariant::with('product.category')->find($item['variant_id']);
                if (!$variant) continue;

                $isGift = ($variant->price == 0) || (isset($item['is_gift']) && $item['is_gift'] == true);
                if ($isGift) continue;

                $itemPrice = $variant->price > 0 && $variant->price < 10000 ? $variant->price * 1000 : $variant->price;
                $itemTotal = $itemPrice * $item['quantity'];

                $rate = $variant->product->loyalty_rate;
                if (is_null($rate)) {
                    $rate = $variant->product->category->loyalty_rate ?? null;
                }
                if (is_null($rate)) {
                    $rate = 0.2; 
                }

                $itemPoints = ceil(($itemTotal * $rate) / 100);
                $totalPointsEarned += $itemPoints;
            }

            if ($totalPointsEarned > 0 && auth()->check()) {
                auth()->user()->increment('reward_points', $totalPointsEarned);
            }

            // Real Payment Integration
            $paymentUrl = null;
            if ($request->payment_method !== 'cod') {
                try {
                    $paymentService = new \App\Services\PaymentService();
                    $paymentUrl = $paymentService->createPaymentUrl($order);
                    \Log::info("Payment URL generated for {$order->order_number}: " . ($paymentUrl ? 'SUCCESS' : 'NULL'));
                } catch (\Exception $e) {
                    \Log::error("Payment URL Exception for {$order->order_number}: " . $e->getMessage());
                }
            }

            // Create initial pending payment log record
            try {
                \App\Models\PaymentLog::create([
                    'order_number' => $order->order_number,
                    'payment_method' => $order->payment_method,
                    'amount' => $order->total_amount,
                    'status' => 'pending',
                    'request_payload' => $request->all(),
                    'tenant_id' => $order->tenant_id,
                ]);
            } catch (\Exception $e) {
                \Log::error("Failed to create PaymentLog for {$order->order_number}: " . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Đơn hàng đã được tạo thành công!',
                'order_number' => $order->order_number,
                'total' => $totalAmount,
                'payment_url' => $paymentUrl,
            ], 201);
        });
    }

    public function index()
    {
        return Order::where('user_id', auth()->id())
            ->withCount('items')
            ->latest()
            ->paginate(10);
    }

    public function show($number)
    {
        return Order::with(['items.variant.product', 'payment', 'store'])
            ->where('order_number', $number)
            ->where('user_id', auth()->id())
            ->firstOrFail();
    }

    /**
     * Unified payment success callback to synchronize order payment state
     */
    public function paymentSuccess(Request $request, $orderNumber)
    {
        \Log::info("paymentSuccess endpoint HIT for order: " . $orderNumber);
        
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        
        $order->update([
            'payment_status' => 'paid',
        ]);

        // Find or create the payment transaction log entry
        $log = \App\Models\PaymentLog::firstOrCreate(
            ['order_number' => $orderNumber],
            [
                'payment_method' => $order->payment_method,
                'amount' => $order->total_amount,
                'request_payload' => [],
                'tenant_id' => $order->tenant_id,
            ]
        );

        $log->update([
            'status' => 'success',
            'response_payload' => $request->all(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Trạng thái thanh toán đơn hàng đã được cập nhật thành công!',
        ]);
    }
}
