<?php

namespace App\Filament\Resources\PromotionTermResource\Pages;

use App\Filament\Resources\PromotionTermResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPromotionTerms extends ListRecords
{
    protected static string $resource = PromotionTermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
