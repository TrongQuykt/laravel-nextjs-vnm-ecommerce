<?php

namespace App\Filament\Resources\AgeGroupResource\Pages;

use App\Filament\Resources\AgeGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageAgeGroups extends ManageRecords
{
    protected static string $resource = AgeGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
