<?php

namespace App\Filament\Resources\CareProductResource\Pages;

use App\Filament\Resources\CareProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCareProducts extends ListRecords
{
    protected static string $resource = CareProductResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
