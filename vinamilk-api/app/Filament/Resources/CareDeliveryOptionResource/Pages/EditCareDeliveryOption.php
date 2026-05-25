<?php

namespace App\Filament\Resources\CareDeliveryOptionResource\Pages;

use App\Filament\Resources\CareDeliveryOptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCareDeliveryOption extends EditRecord
{
    protected static string $resource = CareDeliveryOptionResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
