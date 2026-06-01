<?php

namespace App\Filament\Resources\RolePermissionResource\Pages;

use App\Filament\Resources\RolePermissionResource;
use App\Services\ActivityLogger;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRolePermission extends EditRecord
{
    protected static string $resource = RolePermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function ($record) {
                    ActivityLogger::logDelete('Phân quyền Admin', $record->id, $record->toArray());
                }),
        ];
    }

    protected function afterSave(): void
    {
        ActivityLogger::logUpdate('Phân quyền Admin', $this->record->id, $this->record->getOriginal(), $this->record->toArray());
    }
}
