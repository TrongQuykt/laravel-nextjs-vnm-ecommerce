<?php

namespace App\Filament\Exports;

use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class UserExporter extends Exporter
{
    protected static ?string $model = User::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('name')->label('Họ tên'),
            ExportColumn::make('email')->label('Email'),
            ExportColumn::make('phone')->label('Số điện thoại'),
            ExportColumn::make('reward_points')->label('Điểm thành viên'),
            ExportColumn::make('email_verified_at')->label('Đã xác nhận email')->formatStateUsing(fn ($state) => $state ? 'Có' : 'Không'),
            ExportColumn::make('created_at')->label('Ngày đăng ký')->formatStateUsing(fn ($state) => $state->format('d/m/Y H:i')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your user export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
