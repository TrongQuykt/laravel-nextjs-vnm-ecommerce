<?php

namespace App\Filament\Resources\ProductLineResource\Pages;

use App\Filament\Resources\ProductLineResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageProductLines extends ManageRecords
{
    protected static string $resource = ProductLineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
