<?php

namespace App\Filament\Resources\CareGreetingCardResource\Pages;

use App\Filament\Resources\CareGreetingCardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCareGreetingCards extends ListRecords
{
    protected static string $resource = CareGreetingCardResource::class;
    protected function getHeaderActions(): array { return [Actions\CreateAction::make()]; }
}
