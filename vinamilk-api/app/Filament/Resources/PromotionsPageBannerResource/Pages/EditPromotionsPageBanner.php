<?php

namespace App\Filament\Resources\PromotionsPageBannerResource\Pages;

use App\Filament\Resources\PromotionsPageBannerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPromotionsPageBanner extends EditRecord
{
    protected static string $resource = PromotionsPageBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
