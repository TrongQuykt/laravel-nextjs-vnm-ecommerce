<?php

namespace App\Filament\Resources\LogisticsResource\Pages;

use App\Filament\Resources\LogisticsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLogistics extends ListRecords
{
    protected static string $resource = LogisticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No manual creation allowed
        ];
    }
}
