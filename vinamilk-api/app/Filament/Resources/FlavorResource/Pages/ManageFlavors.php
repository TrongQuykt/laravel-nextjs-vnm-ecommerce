<?php

namespace App\Filament\Resources\FlavorResource\Pages;

use App\Filament\Resources\FlavorResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageFlavors extends ManageRecords
{
    protected static string $resource = FlavorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
