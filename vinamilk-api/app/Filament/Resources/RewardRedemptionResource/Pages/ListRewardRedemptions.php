<?php

namespace App\Filament\Resources\RewardRedemptionResource\Pages;

use App\Filament\Resources\RewardRedemptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRewardRedemptions extends ListRecords
{
    protected static string $resource = RewardRedemptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
