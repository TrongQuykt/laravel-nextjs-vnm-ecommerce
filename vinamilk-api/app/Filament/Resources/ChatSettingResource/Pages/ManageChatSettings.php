<?php

namespace App\Filament\Resources\ChatSettingResource\Pages;

use App\Filament\Resources\ChatSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageChatSettings extends ManageRecords
{
    protected static string $resource = ChatSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
