<?php

namespace App\Filament\Resources\PromotionBannerResource\Pages;

use App\Filament\Resources\PromotionBannerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPromotionBanners extends ListRecords
{
    protected static string $resource = PromotionBannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('visual_builder')
                ->label('Xếp lưới trực quan')
                ->icon('heroicon-o-squares-2x2')
                ->color('success')
                ->url(fn () => PromotionBannerResource::getUrl('grid')),
            Actions\CreateAction::make(),
        ];
    }
}
