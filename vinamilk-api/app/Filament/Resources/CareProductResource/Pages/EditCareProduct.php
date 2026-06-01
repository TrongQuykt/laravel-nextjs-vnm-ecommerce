<?php

namespace App\Filament\Resources\CareProductResource\Pages;

use App\Filament\Resources\CareProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCareProduct extends EditRecord
{
    protected static string $resource = CareProductResource::class;
    protected function getHeaderActions(): array { return [Actions\DeleteAction::make()]; }
}
