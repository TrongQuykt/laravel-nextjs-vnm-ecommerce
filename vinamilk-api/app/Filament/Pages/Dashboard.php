<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;

/**
 * Main Dashboard Page - Filament
 * Nâng cấp với analytics toàn diện
 */
class Dashboard extends BaseDashboard
{
    public function getTitle(): string
    {
        return 'Dashboard';
    }

    /**
     * @return array<class-string<Widget>|WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\StatsOverview::class,
            \App\Filament\Widgets\RevenueChartWidget::class,
            \App\Filament\Widgets\CategoryPerformanceWidget::class,
            \App\Filament\Widgets\TopSellingProductsWidget::class,
            \App\Filament\Widgets\RecentOrdersTable::class,
            \App\Filament\Widgets\RecentActivityWidget::class,
        ];
    }

    /**
     * @return int|string|array
     */
    public function getColumns(): int|string|array
    {
        return [
            'md' => 3,
            'lg' => 4,
        ];
    }
}
