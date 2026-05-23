<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class SalesTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Xu hướng doanh thu';

    protected static ?int $sort = 1;

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Doanh thu (VNĐ)',
                    'data' => [0, 10, 5, 2, 21, 32, 45, 74, 65, 45, 77, 89],
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(37, 99, 235, 0.1)',
                    'borderColor' => '#2563EB',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
