<?php

namespace App\Filament\Resources\VatOrderResource\Pages;

use App\Filament\Resources\VatOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVatOrder extends EditRecord
{
    protected static string $resource = VatOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
