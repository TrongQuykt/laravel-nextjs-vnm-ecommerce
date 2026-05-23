<?php

namespace App\Filament\Resources\MarketingGiftResource\Pages;

use App\Filament\Resources\MarketingGiftResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarketingGift extends EditRecord
{
    protected static string $resource = MarketingGiftResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
