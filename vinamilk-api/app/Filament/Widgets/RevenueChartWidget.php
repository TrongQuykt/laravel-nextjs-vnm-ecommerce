<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Phân tích xu hướng kinh doanh';

    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = true;

    protected static ?string $pollingInterval = '300s';

    // 1. Thêm bộ lọc linh hoạt cho quản trị viên
    protected function getFilters(): ?array
    {
        return [
            '7_days' => '7 ngày gần nhất',
            '30_days' => '30 ngày gần nhất',
            'this_month' => 'Tháng này',
            'last_month' => 'Tháng trước',
        ];
    }

    protected function getData(): array
    {
        // Thiết lập bộ lọc thời gian mặc định hoặc từ request lọc
        $activeFilter = $this->filter ?? '30_days';
        
        $startDate = match ($activeFilter) {
            '7_days' => now()->subDays(6)->startOfDay(),
            'this_month' => now()->startOfMonth(),
            'last_month' => now()->subMonth()->startOfMonth(),
            default => now()->subDays(29)->startOfDay(), // 30_days
        };

        $endDate = match ($activeFilter) {
            'last_month' => now()->subMonth()->endOfMonth(),
            default => now()->endOfDay(),
        };

        try {
            // Lấy dữ liệu thô từ database
            $rawRecords = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CASE WHEN status != "cancelled" THEN total_amount ELSE 0 END) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

            // 2. Kỹ thuật tạo mảng dữ liệu liên tục không đứt gãy (Fill Gaps)
            $processedLabels = [];
            $revenueData = [];
            $ordersData = [];

            $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
            foreach ($period as $date) {
                $formattedDate = $date->format('Y-m-d');
                $processedLabels[] = $date->format('d/m');

                if ($rawRecords->has($formattedDate)) {
                    $revenueData[] = (float) $rawRecords[$formattedDate]->revenue;
                    $ordersData[] = (int) $rawRecords[$formattedDate]->orders;
                } else {
                    $revenueData[] = 0.0;
                    $ordersData[] = 0;
                }
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Doanh thu thực (VNĐ)',
                        'data' => $revenueData,
                        'type' => 'bar', // Chuyển thành Cột (Bar)
                        'backgroundColor' => 'rgba(59, 130, 246, 0.75)', // Màu xanh dương đậm sang trọng
                        'borderColor' => 'rgb(37, 99, 235)',
                        'borderWidth' => 1,
                        'borderRadius' => 4, // Bo góc đầu cột hiện đại
                        'yAxisID' => 'y',
                    ],
                    [
                        'label' => 'Số lượng đơn hàng',
                        'data' => $ordersData,
                        'type' => 'line', // Giữ nguyên Đường (Line) chạy đè lên trên cột
                        'borderColor' => 'rgb(16, 185, 129)', // Màu xanh lá cây
                        'backgroundColor' => 'transparent',
                        'fill' => false,
                        'tension' => 0.35,
                        'borderWidth' => 3,
                        'pointBackgroundColor' => 'rgb(16, 185, 129)',
                        'pointRadius' => 3,
                        'pointHoverRadius' => 6,
                        'yAxisID' => 'y1',
                    ],
                ],
                'labels' => $processedLabels,
            ];
        } catch (\Exception $e) {
            return [
                'datasets' => [],
                'labels' => ['Hệ thống gặp lỗi nạp dữ liệu'],
            ];
        }
    }

    // 3. Sử dụng cấu hình hỗn hợp (Bar-Line combo)
    protected function getType(): string
    {
        return 'bar'; // Bắt buộc khai báo là bar để chứa được cả kiểu dữ liệu đường (line) bên trong
    }

    // 4. Nâng cấp Options với RawJs định dạng số liệu chuẩn
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Giá trị doanh thu',
                    ],
                    'grid' => [
                        'color' => 'rgba(156, 163, 175, 0.1)',
                    ],
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Số lượng đơn',
                    ],
                    'ticks' => [
                        'stepSize' => 1,
                        'precision' => 0,
                    ],
                    'grid' => [
                        'drawOnChartArea' => false,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'maxRotation' => 0,
                        'autoSkip' => true,
                        'maxTicksLimit' => 15,
                    ],
                ],
            ],
        ];
    }
}