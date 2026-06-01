<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Admin Tenant Management Controller
 */
class AdminTenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $tenants,
        ]);
    }

    public function current(Request $request)
    {
        $tenant = $request->user()?->tenant ?? Tenant::first();

        return response()->json([
            'status' => 'success',
            'data' => $tenant,
        ]);
    }

    public function getSettings(Request $request, $tenantId = null)
    {
        $tenant = $tenantId ? Tenant::findOrFail($tenantId) : $request->user()?->tenant ?? Tenant::first();

        return response()->json([
            'status' => 'success',
            'data' => [
                'tenant_id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'domain' => $tenant->domain,
                'settings' => $tenant->theme_config ?? [],
                'is_active' => $tenant->is_active,
            ],
        ]);
    }

    public function updateSettings(Request $request, $tenantId = null)
    {
        $tenant = $tenantId ? Tenant::findOrFail($tenantId) : $request->user()?->tenant ?? Tenant::first();

        $validated = $request->validate([
            'theme_config' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $tenant->update([
            'theme_config' => $validated['theme_config'] ?? $tenant->theme_config,
            'is_active' => $validated['is_active'] ?? $tenant->is_active,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật cấu hình tenant thành công.',
            'data' => [
                'tenant_id' => $tenant->id,
                'settings' => $tenant->theme_config,
            ],
        ]);
    }

    public function getStats(Request $request, $tenantId = null)
    {
        $tenant = $tenantId ? Tenant::findOrFail($tenantId) : $request->user()?->tenant ?? Tenant::first();

        $orderMetrics = Order::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', '!=', 'cancelled')
            ->selectRaw('COUNT(*) as orders_count, SUM(total_price) as total_revenue, AVG(total_price) as avg_order_value')
            ->first();

        $customerCount = User::query()
            ->where('tenant_id', $tenant->id)
            ->count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'total_revenue' => $orderMetrics->total_revenue ?? 0,
                'orders_count' => $orderMetrics->orders_count ?? 0,
                'avg_order_value' => $orderMetrics->avg_order_value ?? 0,
                'customer_count' => $customerCount,
            ],
        ]);
    }
}
