<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        return [
            Stat::make('Doanh thu tổng', '1.28M VNĐ')
                ->description('Tăng 12% so với tháng trước')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Đơn hàng mới', '156')
                ->description('32 đơn hàng đang chờ xử lý')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info')
                ->chart([15, 4, 10, 2, 12, 4, 12]),

            Stat::make('Khách hàng mới', '1,340')
                ->description('Tăng 8% trong tuần này')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success')
                ->chart([10, 15, 8, 12, 9, 14, 10]),

            Stat::make('Tỷ lệ chuyển đổi', '3.2%')
                ->description('Trung bình ngành: 2.5%')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('warning')
                ->chart([2, 3, 2.5, 3.2, 3, 3.5, 3.2]),
        ];
    }
}
