<?php

namespace App\Filament\Resources\SpecialHighlightResource\Pages;

use App\Filament\Resources\SpecialHighlightResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSpecialHighlight extends EditRecord
{
    protected static string $resource = SpecialHighlightResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
