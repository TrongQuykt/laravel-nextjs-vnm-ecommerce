<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MarketingEngine\MarketingEngineService;
use App\Services\MarketingEngine\DTO\CartPayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketingEngineController extends Controller
{
    public function __construct(private readonly MarketingEngineService $engine) {}

    /**
     * POST /api/v1/cart/evaluate
     *
     * Request body:
     * {
     *   "items": [
     *     {"product_id": 1, "category_id": 2, "variant_id": 5, "quantity": 3, "price": 45000}
     *   ],
     *   "coupon_code": "SALE10",       // optional
     *   "payment_method": "cod"         // optional
     * }
     */
    public function evaluate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['required', 'integer'],
            'items.*.category_id'    => ['sometimes', 'integer'],
            'items.*.variant_id'     => ['sometimes', 'integer'],
            'items.*.quantity'       => ['required', 'integer', 'min:1'],
            'items.*.price'          => ['required', 'numeric', 'min:0'],
            'coupon_code'            => ['sometimes', 'nullable', 'string'],
            'payment_method'         => ['sometimes', 'nullable', 'string'],
            'reward_selections'      => ['sometimes', 'nullable', 'array'],
        ]);

        $payload = CartPayload::fromArray([
            ...$validated,
            'user_id' => $request->user()?->id,
            'reward_selections' => $request->input('reward_selections', []),
        ]);

        $enrichedCart = $this->engine->evaluate($payload);

        return response()->json([
            'success' => true,
            'data'    => $enrichedCart->toArray(),
        ]);
    }
}
