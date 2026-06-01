<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CareSubscriptionResource\Pages;
use App\Models\CareSubscription;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CareSubscriptionResource extends Resource
{
    protected static ?string $model = CareSubscription::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Chăm sóc khách hàng';
    protected static ?string $navigationLabel = 'Đăng ký Care';
    protected static ?string $modelLabel = 'Gói đăng ký';

    protected static ?int $navigationSort = 4;

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id'),
            Tables\Columns\TextColumn::make('user.name')->label('Khách'),
            Tables\Columns\TextColumn::make('delivery_count')->label('Lần giao'),
            Tables\Columns\TextColumn::make('total_amount')->money('VND'),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('first_delivery_date')->date('d/m/Y'),
            Tables\Columns\TextColumn::make('paymentOrder.order_number')->label('Mã đơn'),
        ])->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListCareSubscriptions::route('/')];
    }

    public static function canCreate(): bool { return false; }
}
