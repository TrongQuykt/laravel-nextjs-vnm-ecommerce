<?php

namespace App\Filament\Resources\MarketingGiftResource\Pages;

use App\Filament\Resources\MarketingGiftResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarketingGifts extends ListRecords
{
    protected static string $resource = MarketingGiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
