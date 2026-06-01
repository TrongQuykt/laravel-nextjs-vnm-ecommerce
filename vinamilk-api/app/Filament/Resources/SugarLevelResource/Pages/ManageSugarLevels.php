<?php

namespace App\Filament\Resources\SugarLevelResource\Pages;

use App\Filament\Resources\SugarLevelResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSugarLevels extends ManageRecords
{
    protected static string $resource = SugarLevelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
