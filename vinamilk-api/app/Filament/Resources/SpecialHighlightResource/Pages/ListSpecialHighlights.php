<?php

namespace App\Filament\Resources\SpecialHighlightResource\Pages;

use App\Filament\Resources\SpecialHighlightResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSpecialHighlights extends ListRecords
{
    protected static string $resource = SpecialHighlightResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
