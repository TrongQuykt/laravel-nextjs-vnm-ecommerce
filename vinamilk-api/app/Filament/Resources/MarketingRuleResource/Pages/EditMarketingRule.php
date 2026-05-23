<?php

namespace App\Filament\Resources\MarketingRuleResource\Pages;

use App\Filament\Resources\MarketingRuleResource;
use App\Services\MarketingEngine\RuleLoader;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarketingRule extends EditRecord
{
    protected static string $resource = MarketingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->after(fn() => RuleLoader::invalidateCache()),
        ];
    }

    protected function afterSave(): void
    {
        RuleLoader::invalidateCache();
    }
}
