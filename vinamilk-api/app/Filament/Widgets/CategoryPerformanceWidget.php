<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CategoryPerformanceWidget extends ChartWidget
{
    protected static ?string $heading = 'Hiệu suất danh mục (30 ngày)';

    protected static ?int $sort = 5;

    protected static string $color = 'warning';

    protected int | string | array $columnSpan = 'half';

    protected static bool $isLazy = false;

    protected function getData(): array
    {
        try {
            // Get activity logs from last 30 days, grouped by resource_type
            $data = ActivityLog::select(
                DB::raw('resource_type as category'),
                DB::raw('COUNT(*) as operations')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('resource_type')
            ->groupBy('resource_type')
            ->orderByDesc('operations')
            ->limit(10)
            ->get();

            // Simplify resource_type names for display
            $labels = $data->pluck('category')->map(function($type) {
                // Extract simple name from resource_type
                if (str_contains($type, '\\')) {
                    $parts = explode('\\', $type);
                    return end($parts);
                }
                return $type;
            })->toArray();

            return [
                'datasets' => [
                    [
                        'label' => 'Số thao tác',
                        'data' => $data->pluck('operations')->toArray(),
                        'backgroundColor' => [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                            'rgba(20, 184, 166, 0.8)',
                            'rgba(249, 115, 22, 0.8)',
                            'rgba(107, 114, 128, 0.8)',
                            'rgba(234, 88, 12, 0.8)',
                        ],
                    ],
                ],
                'labels' => $labels,
            ];
        } catch (\Exception $e) {
            return [
                'datasets' => [
                    [
                        'label' => 'Số thao tác',
                        'data' => [0],
                        'backgroundColor' => ['rgba(239, 68, 68, 0.8)'],
                    ],
                ],
                'labels' => ['Lỗi'],
            ];
        }
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
        ];
    }
}
