<?php

namespace App\Filament\Resources\PromotionTermResource\Pages;

use App\Filament\Resources\PromotionTermResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPromotionTerm extends EditRecord
{
    protected static string $resource = PromotionTermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
