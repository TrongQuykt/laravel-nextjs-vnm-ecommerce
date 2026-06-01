<?php

namespace App\Filament\Pages;

use App\Models\Order;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class OrderHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Lịch sử đơn hàng';
     protected static ?string $navigationGroup = 'Bán hàng';
    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.pages.order-history';

    protected function getTableQuery(): Builder
    {
        return Order::query()
            ->with(['user', 'items'])
            ->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('order_number')
                ->label('Mã đơn hàng')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('user.name')
                ->label('Khách hàng')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('total_amount')
                ->label('Tổng tiền')
                ->money('VND')
                ->sortable(),
            Tables\Columns\TextColumn::make('status')
                ->label('Trạng thái')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'processing' => 'info',
                    'shipping' => 'primary',
                    'completed' => 'success',
                    'cancelled' => 'danger',
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'pending' => 'Chờ xử lý',
                    'processing' => 'Đang xử lý',
                    'shipping' => 'Đang giao',
                    'completed' => 'Hoàn thành',
                    'cancelled' => 'Đã hủy',
                    default => $state,
                }),
            Tables\Columns\TextColumn::make('order_type')
                ->label('Loại đơn')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'care' => 'success',
                    'regular' => 'primary',
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'care' => 'Care',
                    'regular' => 'Thường',
                    default => $state,
                }),
            Tables\Columns\TextColumn::make('created_at')
                ->label('Ngày tạo')
                ->dateTime('d/m/Y H:i')
                ->sortable(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('status')
                ->label('Trạng thái')
                ->options([
                    'pending' => 'Chờ xử lý',
                    'processing' => 'Đang xử lý',
                    'shipping' => 'Đang giao',
                    'completed' => 'Hoàn thành',
                    'cancelled' => 'Đã hủy',
                ]),
            Tables\Filters\SelectFilter::make('order_type')
                ->label('Loại đơn')
                ->options([
                    'care' => 'Care',
                    'regular' => 'Thường',
                ]),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('view')
                ->label('Xem chi tiết')
                ->icon('heroicon-o-eye')
                ->url(fn (Order $record): string => route('filament.admin.resources.orders.edit', ['record' => $record->id]))
                ->openUrlInNewTab(),
        ];
    }

    public function getViewData(): array
    {
        return [];
    }
}
