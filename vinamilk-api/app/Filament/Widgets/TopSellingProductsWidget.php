<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\TableWidget;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class TopSellingProductsWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Top 10 Sản phẩm bán chạy nhất (30 ngày)';

    protected static bool $isLazy = true;

    protected static ?string $pollingInterval = '300s'; // Cache for 5 minutes

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->select('products.id', 'products.name', 'products.slug')
                    ->selectRaw('COALESCE(COUNT(DISTINCT order_items.order_id), 0) as order_count')
                    ->selectRaw('COALESCE(COUNT(DISTINCT order_items.package_number), 0) as package_count')
                    ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as product_quantity')
                    ->selectRaw('COALESCE(SUM(order_items.total), 0) as total_revenue')
                    ->join('product_variants', 'products.id', '=', 'product_variants.product_id')
                    ->leftJoin('order_items', 'product_variants.id', '=', 'order_items.product_variant_id')
                    ->leftJoin('orders', function($join) {
                        $join->on('order_items.order_id', '=', 'orders.id')
                            ->where('orders.status', '!=', 'cancelled')
                            ->where('orders.created_at', '>=', now()->subDays(30));
                    })
                    ->groupBy('products.id', 'products.name', 'products.slug')
                    ->orderByDesc('order_count')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Sản phẩm')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('order_count')
                    ->label('SL Đơn hàng')
                    ->sortable()
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state))
                    ->color('success'),
                Tables\Columns\TextColumn::make('package_count')
                    ->label('SL Kiện hàng')
                    ->sortable()
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state))
                    ->color('info'),
                Tables\Columns\TextColumn::make('product_quantity')
                    ->label('SL Sản phẩm')
                    ->sortable()
                    ->numeric()
                    ->formatStateUsing(fn ($state) => number_format($state))
                    ->color('warning'),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Doanh thu')
                    ->sortable()
                    ->money('VND')
                    ->color('primary'),
            ])
            ->defaultSort('order_count', 'desc')
            ->paginated(false)
            ->recordAction(null);
    }
}
