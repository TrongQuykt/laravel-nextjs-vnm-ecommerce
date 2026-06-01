<?php

namespace App\Filament\Resources\MegaMenuResource\Pages;

use App\Filament\Resources\MegaMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMegaMenus extends ListRecords
{
    protected static string $resource = MegaMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
