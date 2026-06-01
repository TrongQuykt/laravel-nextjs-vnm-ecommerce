<?php

namespace App\Filament\Resources\VatOrderResource\Pages;

use App\Filament\Resources\VatOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVatOrders extends ListRecords
{
    protected static string $resource = VatOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'has_vat' => \Filament\Resources\Components\Tab::make('Có yêu cầu xuất hóa đơn')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->whereNotNull('invoice_info')->where('invoice_info', '!=', '[]')->where('invoice_info', '!=', '{}'))
                ->icon('heroicon-m-document-check')
                ->badge(\App\Models\Order::whereNotNull('invoice_info')->where('invoice_info', '!=', '[]')->where('invoice_info', '!=', '{}')->count()),
            'no_vat' => \Filament\Resources\Components\Tab::make('Không yêu cầu hóa đơn')
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where(function ($q) {
                    $q->whereNull('invoice_info')->orWhere('invoice_info', '[]')->orWhere('invoice_info', '{}');
                }))
                ->icon('heroicon-m-document-minus'),
            'all' => \Filament\Resources\Components\Tab::make('Tất cả đơn hàng')
                ->icon('heroicon-m-rectangle-stack'),
        ];
    }
}
