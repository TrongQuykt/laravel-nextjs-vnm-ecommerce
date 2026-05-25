<?php

namespace App\Filament\Resources\CarePageSettingResource\Pages;

use App\Filament\Resources\CarePageSettingResource;
use App\Models\CarePageSetting;
use Filament\Resources\Pages\EditRecord;

class ManageCarePage extends EditRecord
{
    protected static string $resource = CarePageSettingResource::class;

    public function mount(int|string $record = null): void
    {
        $this->record = CarePageSetting::firstOrCreate([]);
        parent::mount($this->record->getKey());
    }

    protected function getHeaderActions(): array { return []; }
}
