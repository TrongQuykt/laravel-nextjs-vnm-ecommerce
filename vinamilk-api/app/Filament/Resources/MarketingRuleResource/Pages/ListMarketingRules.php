<?php

namespace App\Filament\Resources\MarketingRuleResource\Pages;

use App\Filament\Resources\MarketingRuleResource;
use App\Services\MarketingEngine\RuleLoader;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarketingRules extends ListRecords
{
    protected static string $resource = MarketingRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
