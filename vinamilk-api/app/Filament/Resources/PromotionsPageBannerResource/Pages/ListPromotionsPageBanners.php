<?php

namespace App\Filament\Resources\PromotionsPageBannerResource\Pages;

use App\Filament\Resources\PromotionsPageBannerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPromotionsPageBanners extends ListRecords
{
    protected static string $resource = PromotionsPageBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
