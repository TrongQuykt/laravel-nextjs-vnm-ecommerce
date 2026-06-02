<?php

namespace App\Filament\Resources\EventNewsResource\Pages;

use App\Filament\Resources\EventNewsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEventNews extends ListRecords
{
    protected static string $resource = EventNewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
