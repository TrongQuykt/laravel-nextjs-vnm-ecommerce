<?php

namespace App\Filament\Resources\PromotionPageSettingResource\Pages;

use App\Filament\Resources\PromotionPageSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPromotionPageSettings extends ListRecords
{
    protected static string $resource = PromotionPageSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
