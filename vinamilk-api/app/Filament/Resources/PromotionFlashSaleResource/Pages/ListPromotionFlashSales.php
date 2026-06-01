<?php

namespace App\Filament\Resources\PromotionFlashSaleResource\Pages;

use App\Filament\Resources\PromotionFlashSaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPromotionFlashSales extends ListRecords
{
    protected static string $resource = PromotionFlashSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
