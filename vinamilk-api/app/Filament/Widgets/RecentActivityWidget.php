<?php

namespace App\Filament\Widgets;

use App\Models\ActivityLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentActivityWidget extends BaseWidget
{
    protected static ?int $sort = 12;

    protected int | string | array $columnSpan = 'full';

    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn () => ActivityLog::query()
                    ->with(['user'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Người dùng')
                    ->searchable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('action')
                    ->label('Hành động')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'create' => 'success',
                        'update' => 'warning',
                        'delete' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match($state) {
                        'create' => 'Tạo mới',
                        'update' => 'Cập nhật',
                        'delete' => 'Xóa',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('resource_type')
                    ->label('Đối tượng')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn ($state, $record) => $state . ' #' . $record->resource_id),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Thời gian')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultPaginationPageOption(5)
            ->paginated([5, 10])
            ->heading('Hoạt động gần đây');
    }
}
