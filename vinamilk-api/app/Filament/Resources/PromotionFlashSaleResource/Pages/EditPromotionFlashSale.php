<?php

namespace App\Filament\Resources\PromotionFlashSaleResource\Pages;

use App\Filament\Resources\PromotionFlashSaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPromotionFlashSale extends EditRecord
{
    protected static string $resource = PromotionFlashSaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
