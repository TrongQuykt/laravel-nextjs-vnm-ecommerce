<?php

namespace App\Filament\Resources\RolePermissionResource\Pages;

use App\Filament\Resources\RolePermissionResource;
use App\Services\ActivityLogger;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateRolePermission extends CreateRecord
{
    protected static string $resource = RolePermissionResource::class;

    protected function afterCreate(): void
    {
        // Sync permissions with Spatie
        $permissions = $this->form->getState()['permissions'] ?? [];
        $this->record->syncPermissions($permissions);

        ActivityLogger::logCreate('Phân quyền Admin', $this->record->id, $this->record->toArray());
    }
}
