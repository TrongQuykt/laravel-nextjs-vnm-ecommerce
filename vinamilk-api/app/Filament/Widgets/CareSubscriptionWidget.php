<?php

namespace App\Filament\Widgets;

use App\Models\CareSubscription;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CareSubscriptionWidget extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'half';

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        try {
            $thisMonth = now()->startOfMonth();
            
            $totalSubscriptions = CareSubscription::count();
            $activeSubscriptions = CareSubscription::where('status', 'active')->count();
            $newSubscriptionsThisMonth = CareSubscription::where('created_at', '>=', $thisMonth)->count();
            $pausedSubscriptions = CareSubscription::where('status', 'paused')->count();
            $cancelledSubscriptions = CareSubscription::where('status', 'cancelled')->count();

            return [
                Stat::make('Vinamilk Care - Tổng đăng ký', $totalSubscriptions)
                    ->description('Tất cả thời gian')
                    ->descriptionIcon('heroicon-o-users')
                    ->color('primary'),
                
                Stat::make('Đang hoạt động', $activeSubscriptions)
                    ->description('Subscription active')
                    ->descriptionIcon('heroicon-o-check-circle')
                    ->color('success'),
                
                Stat::make('Mới tháng này', $newSubscriptionsThisMonth)
                    ->description('Đăng ký mới')
                    ->descriptionIcon('heroicon-o-user-plus')
                    ->color('info'),
                
                Stat::make('Tạm dừng', $pausedSubscriptions)
                    ->description('Subscription paused')
                    ->descriptionIcon('heroicon-o-pause')
                    ->color('warning'),
                
                Stat::make('Đã hủy', $cancelledSubscriptions)
                    ->description('Subscription cancelled')
                    ->descriptionIcon('heroicon-o-x-circle')
                    ->color('danger'),
            ];
        } catch (\Exception $e) {
            return [
                Stat::make('Vinamilk Care', 'Lỗi')
                    ->description('Không thể tải dữ liệu')
                    ->descriptionIcon('heroicon-o-exclamation-triangle')
                    ->color('danger'),
            ];
        }
    }
}
