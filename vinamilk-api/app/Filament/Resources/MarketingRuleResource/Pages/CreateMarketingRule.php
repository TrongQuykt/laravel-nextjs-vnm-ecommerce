<?php

namespace App\Filament\Resources\MarketingRuleResource\Pages;

use App\Filament\Resources\MarketingRuleResource;
use App\Services\MarketingEngine\RuleLoader;
use Filament\Resources\Pages\CreateRecord;

class CreateMarketingRule extends CreateRecord
{
    protected static string $resource = MarketingRuleResource::class;

    protected function afterCreate(): void
    {
        RuleLoader::invalidateCache();
    }
}
