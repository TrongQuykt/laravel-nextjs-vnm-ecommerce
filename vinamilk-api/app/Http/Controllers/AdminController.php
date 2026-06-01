<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function getStats(Request $request)
    {
        $period = $request->get('period', 'month');
        $cacheKey = "admin:stats:{$period}:" . now()->format('Y-m-d-H');

        $stats = Cache::remember($cacheKey, 300, function () use ($period) {
            [$currentStart, $currentEnd, $previousStart, $previousEnd] = $this->getPeriodBounds($period);

            $currentRevenue = Order::where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$currentStart, $currentEnd])
                ->sum('total_amount');

            $currentOrders = Order::whereBetween('created_at', [$currentStart, $currentEnd])->count();

            $currentCustomers = Order::whereBetween('created_at', [$currentStart, $currentEnd])
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count('user_id');

            $previousRevenue = Order::where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$previousStart, $previousEnd])
                ->sum('total_amount');

            $previousOrders = Order::whereBetween('created_at', [$previousStart, $previousEnd])->count();

            $previousCustomers = Order::whereBetween('created_at', [$previousStart, $previousEnd])
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count('user_id');

            $revenueChange = $previousRevenue > 0 ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;
            $ordersChange = $previousOrders > 0 ? (($currentOrders - $previousOrders) / $previousOrders) * 100 : 0;
            $customersChange = $previousCustomers > 0 ? (($currentCustomers - $previousCustomers) / $previousCustomers) * 100 : 0;

            return [
                'total_revenue' => (float) $currentRevenue,
                'total_orders' => (int) $currentOrders,
                'total_customers' => (int) $currentCustomers,
                'avg_order_value' => $currentOrders > 0 ? $currentRevenue / $currentOrders : 0,
                'period' => $period,
                'currency' => 'VND',
                'change_percent' => [
                    'revenue' => round($revenueChange, 2),
                    'orders' => round($ordersChange, 2),
                    'customers' => round($customersChange, 2),
                ],
                'pending_orders' => Order::where('status', 'pending')->count(),
                'shipping_orders' => Order::where('status', 'shipping')->count(),
                'completed_orders' => Order::where('status', 'completed')->count(),
                'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $stats,
        ]);
    }

    /**
     * Get sales chart data
     */
    public function getSalesChart(Request $request)
    {
        $days = $request->get('days', 30);
        $cacheKey = "admin:sales-chart:{$days}:" . now()->format('Y-m-d-H');

        $data = Cache::remember($cacheKey, 600, function () use ($days) {
            $chartRows = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CASE WHEN status != "cancelled" THEN total_amount ELSE 0 END) as revenue'),
                DB::raw('COUNT(CASE WHEN status != "cancelled" THEN 1 END) as orders'),
                DB::raw('COUNT(DISTINCT user_id) as customers')
            )
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

            $filledData = [];
            for ($i = $days; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $row = $chartRows->firstWhere('date', $date);

                $filledData[] = [
                    'date' => $date,
                    'revenue' => (float) ($row->revenue ?? 0),
                    'orders' => (int) ($row->orders ?? 0),
                    'customers' => (int) ($row->customers ?? 0),
                ];
            }

            return $filledData;
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    /**
     * Get top selling products
     */
    public function getTopProducts(Request $request)
    {
        $limit = $request->get('limit', 10);

        $products = Product::query()
            ->select('products.id', 'products.name', 'products.sku', 'products.main_image as image_url')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as quantity_sold')
            ->selectRaw('COALESCE(SUM(order_items.total), 0) as revenue')
            ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->leftJoin('order_items', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->leftJoin('orders', function ($join) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->where('orders.status', '!=', 'cancelled')
                    ->where('orders.created_at', '>=', now()->subDays(30));
            })
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.main_image')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                $previousRevenue = Product::query()
                    ->selectRaw('COALESCE(SUM(order_items.total), 0) as previous_revenue')
                    ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
                    ->leftJoin('order_items', 'product_variants.id', '=', 'order_items.product_variant_id')
                    ->leftJoin('orders', function ($join) {
                        $join->on('order_items.order_id', '=', 'orders.id')
                            ->where('orders.status', '!=', 'cancelled')
                            ->where('orders.created_at', '>=', now()->subDays(60))
                            ->where('orders.created_at', '<', now()->subDays(30));
                    })
                    ->where('products.id', $product->id)
                    ->groupBy('products.id')
                    ->first()?->previous_revenue ?? 0;

                $currentRevenue = (float) ($product->revenue ?? 0);
                $growthPercent = $previousRevenue > 0 ? (($currentRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'image_url' => $product->image_url,
                    'quantity_sold' => (int) ($product->quantity_sold ?? 0),
                    'revenue' => (float) $currentRevenue,
                    'growth_percent' => round($growthPercent, 2),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $products,
        ]);
    }

    /**
     * Get top customers
     */
    public function getTopCustomers(Request $request)
    {
        $limit = $request->get('limit', 10);

        $customers = User::select('users.id', 'users.name', 'users.email', 'users.phone')
            ->selectRaw('COUNT(orders.id) as orders_count')
            ->selectRaw('COALESCE(SUM(orders.total_amount), 0) as total_spent')
            ->leftJoin('orders', function($join) {
                $join->on('users.id', '=', 'orders.user_id')
                    ->where('orders.status', '!=', 'cancelled');
            })
            ->groupBy('users.id', 'users.name', 'users.email', 'users.phone')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $customers,
        ]);
    }

    /**
     * Get order status distribution
     */
    public function getOrderStatusDistribution()
    {
        $total = Order::count();

        $distribution = Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) use ($total) {
                return [
                    'status' => $item->status,
                    'count' => (int) $item->count,
                    'percentage' => $total > 0 ? round(($item->count / $total) * 100, 2) : 0,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $distribution,
        ]);
    }

    /**
     * Get customer acquisition data
     */
    public function getCustomerAcquisition(Request $request)
    {
        $days = $request->get('days', 30);

        $data = User::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
        ->where('created_at', '>=', now()->subDays($days))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        $cumulative = 0;
        $chartData = $data->map(function ($row) use (&$cumulative) {
            $cumulative += $row->count;

            return [
                'date' => $row->date,
                'new_customers' => (int) $row->count,
                'cumulative_total' => $cumulative,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $chartData,
        ]);
    }

    /**
     * Get revenue forecast
     */
    public function getRevenueForecast(Request $request)
    {
        $days = $request->get('days', 7);
        
        // Simple forecast based on last 30 days average
        $last30DaysRevenue = Order::where('created_at', '>=', now()->subDays(30))
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');
        
        $dailyAverage = $last30DaysRevenue / 30;
        
        $forecast = [];
        for ($i = 1; $i <= $days; $i++) {
            $forecast[] = [
                'date' => now()->addDays($i)->format('Y-m-d'),
                'revenue' => $dailyAverage,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $forecast,
        ]);
    }

    /**
     * Get server health metrics
     */
    public function getServerHealth()
    {
        $health = [
            'cpu_usage' => $this->getCpuUsage(),
            'memory_usage' => $this->getMemoryUsage(),
            'disk_usage' => $this->getDiskUsage(),
            'uptime' => $this->getUptime(),
            'status' => 'healthy',
        ];

        return response()->json([
            'status' => 'success',
            'data' => $health,
        ]);
    }

    /**
     * Get API metrics
     */
    public function getApiMetrics()
    {
        // This would typically come from a monitoring service
        $metrics = [
            'endpoints' => [
                [
                    'endpoint' => '/api/v1/admin/dashboard/stats',
                    'method' => 'GET',
                    'response_time' => 150,
                    'error_rate' => 0.5,
                ],
                [
                    'endpoint' => '/api/v1/admin/dashboard/sales-chart',
                    'method' => 'GET',
                    'response_time' => 200,
                    'error_rate' => 0.2,
                ],
            ],
            'total_requests' => 1500,
            'average_response_time' => 175,
            'error_rate' => 0.3,
        ];

        return response()->json([
            'status' => 'success',
            'data' => $metrics,
        ]);
    }

    // Helper methods
    private function getPeriodBounds(string $period): array
    {
        return match ($period) {
            'today' => [
                now()->startOfDay(),
                now()->endOfDay(),
                now()->subDay()->startOfDay(),
                now()->subDay()->endOfDay(),
            ],
            'week' => [
                now()->startOfWeek(),
                now()->endOfWeek(),
                now()->subWeek()->startOfWeek(),
                now()->subWeek()->endOfWeek(),
            ],
            'year' => [
                now()->startOfYear(),
                now()->endOfYear(),
                now()->subYear()->startOfYear(),
                now()->subYear()->endOfYear(),
            ],
            default => [
                now()->startOfMonth(),
                now()->endOfMonth(),
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
            ],
        };
    }

    private function getTotalRevenue($period)
    {
        $query = Order::where('status', '!=', 'cancelled');

        return match($period) {
            'today' => $query->where('created_at', '>=', now()->startOfDay())->sum('total_amount'),
            'week' => $query->where('created_at', '>=', now()->startOfWeek())->sum('total_amount'),
            'month' => $query->where('created_at', '>=', now()->startOfMonth())->sum('total_amount'),
            'year' => $query->where('created_at', '>=', now()->startOfYear())->sum('total_amount'),
            default => $query->sum('total_amount'),
        };
    }

    private function getTotalOrders($period)
    {
        $query = Order::query();
        
        return match($period) {
            'today' => $query->where('created_at', '>=', now()->startOfDay())->count(),
            'week' => $query->where('created_at', '>=', now()->startOfWeek())->count(),
            'month' => $query->where('created_at', '>=', now()->startOfMonth())->count(),
            'year' => $query->where('created_at', '>=', now()->startOfYear())->count(),
            default => $query->count(),
        };
    }

    private function getTotalCustomers($period)
    {
        $query = User::query();
        
        return match($period) {
            'today' => $query->where('created_at', '>=', now()->startOfDay())->count(),
            'week' => $query->where('created_at', '>=', now()->startOfWeek())->count(),
            'month' => $query->where('created_at', '>=', now()->startOfMonth())->count(),
            'year' => $query->where('created_at', '>=', now()->startOfYear())->count(),
            default => $query->count(),
        };
    }

    private function getAverageOrderValue($period)
    {
        $query = Order::where('status', '!=', 'cancelled');
        
        $totalRevenue = match($period) {
            'today' => $query->where('created_at', '>=', now()->startOfDay())->sum('total_amount'),
            'week' => $query->where('created_at', '>=', now()->startOfWeek())->sum('total_amount'),
            'month' => $query->where('created_at', '>=', now()->startOfMonth())->sum('total_amount'),
            'year' => $query->where('created_at', '>=', now()->startOfYear())->sum('total_amount'),
            default => $query->sum('total_amount'),
        };

        $totalOrders = match($period) {
            'today' => Order::where('created_at', '>=', now()->startOfDay())->count(),
            'week' => Order::where('created_at', '>=', now()->startOfWeek())->count(),
            'month' => Order::where('created_at', '>=', now()->startOfMonth())->count(),
            'year' => Order::where('created_at', '>=', now()->startOfYear())->count(),
            default => Order::count(),
        };

        return $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
    }

    private function getCpuUsage()
    {
        // Simplified CPU usage calculation
        if (PHP_OS_FAMILY === 'Windows') {
            $load = sys_getloadavg();
            return $load[0] ?? 0;
        }
        return 0;
    }

    private function getMemoryUsage()
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $memoryLimitBytes = $memoryLimit === '-1' ? PHP_INT_MAX : $this->convertToBytes($memoryLimit);
        
        return ($memoryUsage / $memoryLimitBytes) * 100;
    }

    private function getDiskUsage()
    {
        $freeSpace = disk_free_space('/');
        $totalSpace = disk_total_space('/');
        
        return $totalSpace > 0 ? (($totalSpace - $freeSpace) / $totalSpace) * 100 : 0;
    }

    private function getUptime()
    {
        // Simplified uptime calculation
        return time() - filectime('/proc/uptime') ?? 0;
    }

    private function convertToBytes($value)
    {
        $unit = strtolower(substr($value, -1));
        $value = (int)$value;
        
        return match($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }
}
