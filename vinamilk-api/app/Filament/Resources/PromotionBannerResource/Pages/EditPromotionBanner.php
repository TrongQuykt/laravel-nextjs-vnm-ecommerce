<?php

namespace App\Filament\Resources\PromotionBannerResource\Pages;

use App\Filament\Resources\PromotionBannerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPromotionBanner extends EditRecord
{
    protected static string $resource = PromotionBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
