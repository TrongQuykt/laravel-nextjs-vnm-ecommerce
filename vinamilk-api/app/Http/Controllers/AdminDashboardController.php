<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Admin Dashboard Controller
 * Quản lý toàn bộ admin API
 */
class AdminDashboardController extends Controller
{
    /**
     * Get dashboard statistics
     * GET /api/v1/admin/dashboard/stats?period=month
     */
    public function getStats(Request $request)
    {
        $period = $request->query('period', 'month');
        
        $dates = match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        [$startDate, $endDate] = $dates;
        [$prevStartDate, $prevEndDate] = $this->getPreviousPeriod($period);

        // Current period
        $currentRevenue = Order::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'cancelled')
            ->sum(DB::raw('total_price'));

        $currentOrders = Order::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $currentCustomers = User::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Previous period
        $prevRevenue = Order::query()
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->where('status', '!=', 'cancelled')
            ->sum(DB::raw('total_price'));

        $prevOrders = Order::query()
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->count();

        $prevCustomers = User::query()
            ->whereBetween('created_at', [$prevStartDate, $prevEndDate])
            ->count();

        // Calculate changes
        $revenueChange = $prevRevenue > 0 
            ? (($currentRevenue - $prevRevenue) / $prevRevenue) * 100 
            : 0;

        $ordersChange = $prevOrders > 0 
            ? (($currentOrders - $prevOrders) / $prevOrders) * 100 
            : 0;

        $customersChange = $prevCustomers > 0 
            ? (($currentCustomers - $prevCustomers) / $prevCustomers) * 100 
            : 0;

        $avgOrderValue = $currentOrders > 0 ? $currentRevenue / $currentOrders : 0;

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_revenue' => $currentRevenue,
                'total_orders' => $currentOrders,
                'total_customers' => $currentCustomers,
                'avg_order_value' => $avgOrderValue,
                'period' => $period,
                'currency' => 'VND',
                'change_percent' => [
                    'revenue' => round($revenueChange, 2),
                    'orders' => round($ordersChange, 2),
                    'customers' => round($customersChange, 2),
                ],
            ],
        ]);
    }

    /**
     * Get sales chart data
     * GET /api/v1/admin/dashboard/sales-chart?days=30
     */
    public function getSalesChart(Request $request)
    {
        $days = $request->query('days', 30);
        $startDate = now()->subDays($days)->startOfDay();

        $data = Order::query()
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(total_price) as revenue, COUNT(*) as orders, COUNT(DISTINCT user_id) as customers')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill missing dates with 0
        $chartData = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $existing = $data->firstWhere('date', $date);
            
            $chartData[] = [
                'date' => $date,
                'revenue' => $existing?->revenue ?? 0,
                'orders' => $existing?->orders ?? 0,
                'customers' => $existing?->customers ?? 0,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $chartData,
        ]);
    }

    /**
     * Get top products
     * GET /api/v1/admin/dashboard/top-products?limit=10
     */
    public function getTopProducts(Request $request)
    {
        $limit = $request->query('limit', 10);

        $products = Product::query()
            ->with('variants')
            ->selectRaw('products.id, products.name, products.sku, products.main_image as image_url, SUM(order_items.quantity) as quantity_sold, SUM(order_items.quantity * order_items.price) as revenue')
            ->leftJoin('product_variants', 'product_variants.product_id', '=', 'products.id')
            ->leftJoin('order_items', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.main_image')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                $prevRevenue = Product::query()
                    ->selectRaw('SUM(order_items.quantity * order_items.price) as revenue')
                    ->leftJoin('product_variants', 'product_variants.product_id', '=', 'products.id')
                    ->leftJoin('order_items', 'order_items.product_variant_id', '=', 'product_variants.id')
                    ->where('products.id', $product->id)
                    ->where('order_items.created_at', '<', now()->subMonth())
                    ->first()?->revenue ?? 0;

                $currentRevenue = $product->revenue ?? 0;
                $growth = $prevRevenue > 0 
                    ? (($currentRevenue - $prevRevenue) / $prevRevenue) * 100 
                    : 0;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'image_url' => $product->image_url,
                    'quantity_sold' => $product->quantity_sold ?? 0,
                    'revenue' => $product->revenue ?? 0,
                    'growth_percent' => round($growth, 2),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $products,
        ]);
    }

    /**
     * Get top customers
     * GET /api/v1/admin/dashboard/top-customers?limit=10
     */
    public function getTopCustomers(Request $request)
    {
        $limit = $request->query('limit', 10);

        $customers = User::query()
            ->selectRaw('users.*, COUNT(orders.id) as orders_count, SUM(orders.total_price) as total_spent, MAX(orders.created_at) as last_order_date')
            ->leftJoin('orders', 'orders.user_id', '=', 'users.id')
            ->where('orders.status', '!=', 'cancelled')
            ->groupBy('users.id')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar_url' => $user->avatar_url,
                    'total_spent' => $user->total_spent ?? 0,
                    'orders_count' => $user->orders_count ?? 0,
                    'last_order_date' => $user->last_order_date?->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $customers,
        ]);
    }

    /**
     * Get order status distribution
     * GET /api/v1/admin/dashboard/order-status-distribution
     */
    public function getOrderStatusDistribution()
    {
        $total = Order::count();

        $statuses = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->map(function ($item) use ($total) {
                return [
                    'status' => $item->status,
                    'count' => $item->count,
                    'percentage' => round(($item->count / $total) * 100, 2),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $statuses,
        ]);
    }

    /**
     * Get customer acquisition trend
     * GET /api/v1/admin/dashboard/customer-acquisition?days=30
     */
    public function getCustomerAcquisition(Request $request)
    {
        $days = $request->query('days', 30);
        $startDate = now()->subDays($days)->startOfDay();

        $data = User::query()
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as new_customers')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill missing dates and calculate cumulative
        $chartData = [];
        $cumulative = User::where('created_at', '<', $startDate)->count();

        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $existing = $data->firstWhere('date', $date);
            $newCount = $existing?->new_customers ?? 0;
            $cumulative += $newCount;

            $chartData[] = [
                'date' => $date,
                'new_customers' => $newCount,
                'cumulative_total' => $cumulative,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $chartData,
        ]);
    }

    /**
     * Helper: Get previous period dates
     */
    private function getPreviousPeriod($period)
    {
        return match ($period) {
            'today' => [
                now()->subDay()->startOfDay(),
                now()->subDay()->endOfDay(),
            ],
            'week' => [
                now()->subWeek()->startOfWeek(),
                now()->subWeek()->endOfWeek(),
            ],
            'year' => [
                now()->subYear()->startOfYear(),
                now()->subYear()->endOfYear(),
            ],
            default => [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ],
        };
    }
}
