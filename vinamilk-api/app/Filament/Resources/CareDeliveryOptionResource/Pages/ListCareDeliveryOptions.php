<?php

namespace App\Filament\Resources\CareDeliveryOptionResource\Pages;

use App\Filament\Resources\CareDeliveryOptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCareDeliveryOptions extends ListRecords
{
    protected static string $resource = CareDeliveryOptionResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
