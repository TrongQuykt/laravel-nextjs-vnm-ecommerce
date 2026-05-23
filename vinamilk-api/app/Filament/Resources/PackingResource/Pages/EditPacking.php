<?php

namespace App\Filament\Resources\PackingResource\Pages;

use App\Filament\Resources\PackingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPacking extends EditRecord
{
    protected static string $resource = PackingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
