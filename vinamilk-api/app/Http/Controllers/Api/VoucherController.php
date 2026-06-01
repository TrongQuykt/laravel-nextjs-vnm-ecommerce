<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VoucherController extends Controller
{
    /**
     * GET /v1/vouchers
     * Trả về danh sách voucher kèm trạng thái eligible theo giỏ hàng hiện tại.
     * Yêu cầu đăng nhập (auth:sanctum).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $rawTotal = $request->query('cart_total', '0');
        // Chuyển về số thực. Ví dụ "443.97" -> 443.97
        $cartTotal = floatval($rawTotal);
        
        // Nếu giá trị nhỏ hơn 10.000, khả năng cao là đang ở đơn vị "ngàn đồng", cần nhân 1000
        if ($cartTotal > 0 && $cartTotal < 10000) {
            $cartTotal *= 1000;
        }
        
        $rawItems = json_decode($request->query('cart_items', '[]'), true) ?? [];
        $cartItems = collect($rawItems)->map(function($item) {
            $price = floatval($item['price']);
            if ($price > 0 && $price < 10000) $price *= 1000;
            $item['price'] = $price;
            return $item;
        })->toArray();

        Log::info('Voucher API Normalized', ['total' => $cartTotal]);

        $vouchers = Voucher::where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $result = $vouchers->map(function (Voucher $voucher) use ($cartTotal, $cartItems, $user) {
            $eligibility = $voucher->checkEligibility($cartTotal, $cartItems, $user?->id);
            return [
                'id'                  => $voucher->id,
                'code'                => $voucher->code,
                'name'                => $voucher->name,
                'description'         => $voucher->description,
                'banner_image'        => $voucher->banner_image
                    ? asset('storage/' . $voucher->banner_image)
                    : null,
                'type'                => $voucher->type,
                'discount_value'      => $voucher->discount_value,
                'max_discount_amount' => $voucher->max_discount_amount,
                'min_order_amount'    => $voucher->min_order_amount,
                'applicable_product_ids' => $voucher->applicable_product_ids,
                'total_quantity'      => $voucher->total_quantity,
                'used_count'          => $voucher->used_count,
                'starts_at'           => $voucher->starts_at?->toISOString(),
                'expires_at'          => $voucher->expires_at?->toISOString(),
                'is_eligible'         => $eligibility['eligible'],
                'ineligible_reason'   => $eligibility['reason'],
                'discount_amount'     => $eligibility['discount'],
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $result,
        ]);
    }

    /**
     * POST /v1/vouchers/apply
     * Validate & áp dụng voucher code. Trả về discount_amount.
     */
    public function apply(Request $request)
    {
        $request->validate([
            'code'       => 'required|string',
            'cart_total' => 'required|numeric|min:0',
            'cart_items' => 'array',
        ]);

        $user    = $request->user();
        $voucher = Voucher::where('code', $request->code)->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Mã voucher không tồn tại.',
            ], 404);
        }

        // Làm sạch dữ liệu và chuẩn hóa đơn vị (nhân 1000 nếu là dạng "ngàn đồng")
        $cartTotal = floatval($request->cart_total);
        if ($cartTotal < 10000) $cartTotal *= 1000;

        $cartItems = collect($request->cart_items ?? [])->map(function($item) {
            $price = floatval($item['price']);
            if ($price < 10000) $price *= 1000;
            $item['price'] = $price;
            return $item;
        })->toArray();

        $eligibility = $voucher->checkEligibility(
            $cartTotal,
            $cartItems,
            $user?->id
        );

        if (!$eligibility['eligible']) {
            return response()->json([
                'success' => false,
                'message' => $eligibility['reason'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'voucher'         => [
                    'id'                  => $voucher->id,
                    'code'                => $voucher->code,
                    'name'                => $voucher->name,
                    'type'                => $voucher->type,
                    'discount_value'      => $voucher->discount_value,
                    'max_discount_amount' => $voucher->max_discount_amount,
                ],
                'discount_amount' => $eligibility['discount'],
            ],
            'message' => 'Áp dụng voucher thành công!',
        ]);
    }

    /**
     * POST /v1/vouchers/validate-code
     * Guest-safe validation để nhập mã thủ công (không yêu cầu auth).
     */
    public function validateCode(Request $request)
    {
        $request->validate([
            'code'       => 'required|string',
            'cart_total' => 'required|numeric|min:0',
            'cart_items' => 'array',
        ]);

        $voucher = Voucher::where('code', $request->code)->first();

        if (!$voucher) {
            return response()->json(['success' => false, 'message' => 'Mã voucher không tồn tại.'], 404);
        }

        $userId = $request->user()?->id;

        // Làm sạch dữ liệu và chuẩn hóa đơn vị (nhân 1000 nếu là dạng "ngàn đồng")
        $cartTotal = floatval($request->cart_total);
        if ($cartTotal < 10000) $cartTotal *= 1000;

        $cartItems = collect($request->cart_items ?? [])->map(function($item) {
            $price = floatval($item['price']);
            if ($price < 10000) $price *= 1000;
            $item['price'] = $price;
            return $item;
        })->toArray();

        $eligibility = $voucher->checkEligibility(
            $cartTotal,
            $cartItems,
            $userId
        );

        if (!$eligibility['eligible']) {
            return response()->json(['success' => false, 'message' => $eligibility['reason']], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'voucher'         => [
                    'id'                  => $voucher->id,
                    'code'                => $voucher->code,
                    'name'                => $voucher->name,
                    'type'                => $voucher->type,
                    'discount_value'      => $voucher->discount_value,
                    'max_discount_amount' => $voucher->max_discount_amount,
                ],
                'discount_amount' => $eligibility['discount'],
            ],
            'message' => 'Voucher hợp lệ!',
        ]);
    }
}
