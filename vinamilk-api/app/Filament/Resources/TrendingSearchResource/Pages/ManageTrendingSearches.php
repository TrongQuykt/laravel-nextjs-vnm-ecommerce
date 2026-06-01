<?php

namespace App\Filament\Resources\TrendingSearchResource\Pages;

use App\Filament\Resources\TrendingSearchResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTrendingSearches extends ManageRecords
{
    protected static string $resource = TrendingSearchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
