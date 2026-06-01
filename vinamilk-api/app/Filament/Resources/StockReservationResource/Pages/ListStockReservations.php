<?php

namespace App\Filament\Resources\StockReservationResource\Pages;

use App\Filament\Resources\StockReservationResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockReservations extends ListRecords
{
    protected static string $resource = StockReservationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
