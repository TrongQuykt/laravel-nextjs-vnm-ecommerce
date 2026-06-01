<?php

namespace App\Filament\Resources\PromotionCampaignResource\Pages;

use App\Filament\Resources\PromotionCampaignResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePromotionCampaigns extends ManageRecords
{
    protected static string $resource = PromotionCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
