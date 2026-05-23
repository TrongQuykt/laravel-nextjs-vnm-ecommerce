<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;

class ShippingMethodController extends Controller
{
    public function index()
    {
        // Self-healing: Ensure GHN is registered in the database
        ShippingMethod::firstOrCreate(
            ['provider' => 'ghn'],
            [
                'tenant_id' => 1,
                'name' => 'Giao hàng nhanh GHN (Sandbox)',
                'base_cost' => 0.00,
                'is_active' => true,
            ]
        );

        // Hide 'ghn' from client-facing checkout options
        $methods = ShippingMethod::where('is_active', true)
            ->where('provider', '!=', 'ghn')
            ->get();

        return response()->json([
            'data' => $methods
        ]);
    }

    public function calculateFee(Request $request)
    {
        $request->validate([
            'province' => 'nullable|string',
            'district' => 'nullable|string',
            'ward' => 'nullable|string',
            'provider' => 'required|string',
        ]);

        $provider = $request->provider;
        
        // Fetch static base cost directly from database
        $method = ShippingMethod::where('provider', $provider)->first();
        $cost = $method ? (float)$method->base_cost : 0.00;

        return response()->json([
            'success' => true,
            'fee' => $cost
        ]);
    }
}
