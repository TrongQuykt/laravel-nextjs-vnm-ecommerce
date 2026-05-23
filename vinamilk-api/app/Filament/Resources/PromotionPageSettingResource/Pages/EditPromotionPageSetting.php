<?php

namespace App\Filament\Resources\PromotionPageSettingResource\Pages;

use App\Filament\Resources\PromotionPageSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPromotionPageSetting extends EditRecord
{
    protected static string $resource = PromotionPageSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
