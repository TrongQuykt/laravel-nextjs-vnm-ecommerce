<?php

namespace App\Filament\Resources\PackingResource\Pages;

use App\Filament\Resources\PackingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPackings extends ListRecords
{
    protected static string $resource = PackingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No manual creation allowed
        ];
    }
}
