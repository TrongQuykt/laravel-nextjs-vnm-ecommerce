<?php

namespace App\Filament\Resources\NutritionalNeedResource\Pages;

use App\Filament\Resources\NutritionalNeedResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageNutritionalNeeds extends ManageRecords
{
    protected static string $resource = NutritionalNeedResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
