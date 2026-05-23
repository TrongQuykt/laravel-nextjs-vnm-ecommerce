<?php

namespace App\Filament\Resources\SupportPageResource\Pages;

use App\Filament\Resources\SupportPageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSupportPage extends EditRecord
{
    protected static string $resource = SupportPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
