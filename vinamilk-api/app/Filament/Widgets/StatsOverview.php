<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = true;

    protected static ?string $pollingInterval = '300s'; // Cache for 5 minutes

    protected function getStats(): array
    {
        try {
            $today = now()->startOfDay();
            $thisMonth = now()->startOfMonth();
            $yesterday = now()->subDay()->startOfDay();
            $lastMonth = now()->subMonth()->startOfMonth();

            // Order statistics
            $totalOrders = Order::count();
            $todayOrders = Order::where('created_at', '>=', $today)->count();
            $yesterdayOrders = Order::whereBetween('created_at', [$yesterday, $today])->count();
            $thisMonthOrders = Order::where('created_at', '>=', $thisMonth)->count();
            $lastMonthOrders = Order::whereBetween('created_at', [$lastMonth, $thisMonth])->count();

            // Revenue statistics
            $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total_amount');
            $todayRevenue = Order::where('created_at', '>=', $today)
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount');
            $yesterdayRevenue = Order::whereBetween('created_at', [$yesterday, $today])
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount');
            $thisMonthRevenue = Order::where('created_at', '>=', $thisMonth)
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount');
            $lastMonthRevenue = Order::whereBetween('created_at', [$lastMonth, $thisMonth])
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount');
            $last30DaysRevenue = Order::where('created_at', '>=', now()->subDays(30))
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount');

            // Order status
            $pendingOrders = Order::where('status', 'pending')->count();
            $processingOrders = Order::where('status', 'processing')->count();
            $shippingOrders = Order::where('status', 'shipping')->count();
            $completedOrders = Order::where('status', 'completed')->count();
            $cancelledOrders = Order::where('status', 'cancelled')->count();

            // Calculate growth rates
            $orderGrowth = $yesterdayOrders > 0 ? (($todayOrders - $yesterdayOrders) / $yesterdayOrders) * 100 : 0;
            $revenueGrowth = $yesterdayRevenue > 0 ? (($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100 : 0;
            $monthlyRevenueGrowth = $lastMonthRevenue > 0 ? (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 0;

            // Product statistics
            $totalProducts = Product::count();
            $activeProducts = Product::where('status', 'published')->count();
            $featuredProducts = Product::where('is_home_featured', true)->count();

            // Customer statistics
            $totalCustomers = User::count();
            $newCustomersThisMonth = User::where('created_at', '>=', $thisMonth)->count();

            return [
                // DOANH THU
                Stat::make('Tổng doanh thu', number_format($totalRevenue, 0, ',', '.') . ' đ')
                    ->description('Tất cả thời gian')
                    ->descriptionIcon('heroicon-o-currency-dollar')
                    ->color('info'),

                Stat::make('Doanh thu 30 ngày', number_format($last30DaysRevenue, 0, ',', '.') . ' đ')
                    ->description('Khớp với biểu đồ')
                    ->descriptionIcon('heroicon-o-chart-bar')
                    ->color('info'),

                Stat::make('Doanh thu hôm nay', number_format($todayRevenue, 0, ',', '.') . ' đ')
                    ->description($revenueGrowth >= 0 ? "+{$revenueGrowth}%" : "{$revenueGrowth}%")
                    ->descriptionIcon($revenueGrowth >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                    ->color($revenueGrowth >= 0 ? 'success' : 'danger'),

                

                // ĐƠN HÀNG
                Stat::make('Tổng đơn hàng', $totalOrders)
                    ->description('Tất cả thời gian')
                    ->descriptionIcon('heroicon-o-shopping-cart')
                    ->color('primary'),

                Stat::make('Đơn hàng hôm nay', $todayOrders)
                    ->description($orderGrowth >= 0 ? "+{$orderGrowth}%" : "{$orderGrowth}%")
                    ->descriptionIcon($orderGrowth >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
                    ->color($orderGrowth >= 0 ? 'success' : 'danger'),

                Stat::make('Đơn hàng tháng này', $thisMonthOrders)
                    ->description('Từ đầu tháng')
                    ->descriptionIcon('heroicon-o-calendar-days')
                    ->color('warning'),

                Stat::make('Đơn chờ xử lý', $pendingOrders)
                    ->description('Cần xử lý')
                    ->descriptionIcon('heroicon-o-clock')
                    ->color('danger'),

                Stat::make('Đang xử lý', $processingOrders)
                    ->description('Đang chuẩn bị')
                    ->descriptionIcon('heroicon-o-arrow-path')
                    ->color('info'),

                Stat::make('Đang giao', $shippingOrders)
                    ->description('Đang vận chuyển')
                    ->descriptionIcon('heroicon-o-truck')
                    ->color('warning'),

                Stat::make('Hoàn thành', $completedOrders)
                    ->description('Đã giao thành công')
                    ->descriptionIcon('heroicon-o-check-circle')
                    ->color('success'),

                Stat::make('Đã hủy', $cancelledOrders)
                    ->description('Đơn bị hủy')
                    ->descriptionIcon('heroicon-o-x-circle')
                    ->color('gray'),

                // SẢN PHẨM
                Stat::make('Tổng sản phẩm', $totalProducts)
                    ->description('Tất cả sản phẩm')
                    ->descriptionIcon('heroicon-o-cube')
                    ->color('primary'),

                Stat::make('Sản phẩm đang bán', $activeProducts)
                    ->description('Đã xuất bản')
                    ->descriptionIcon('heroicon-o-check')
                    ->color('success'),

                Stat::make('Sản phẩm nổi bật', $featuredProducts)
                    ->description('Hiển thị trang chủ')
                    ->descriptionIcon('heroicon-o-star')
                    ->color('warning'),

                // KHÁCH HÀNG
                Stat::make('Tổng khách hàng', $totalCustomers)
                    ->description($newCustomersThisMonth . ' khách mới tháng này')
                    ->descriptionIcon('heroicon-m-user-group')
                    ->color('primary'),

                Stat::make('Khách hàng mới tháng này', $newCustomersThisMonth)
                    ->description('Đăng ký mới')
                    ->descriptionIcon('heroicon-o-user-plus')
                    ->color('success'),
            ];
        } catch (\Exception $e) {
            return [
                Stat::make('Lỗi', 'Không thể tải dữ liệu')
                    ->description('Vui lòng kiểm tra database')
                    ->descriptionIcon('heroicon-o-exclamation-triangle')
                    ->color('danger'),
            ];
        }
    }
}
