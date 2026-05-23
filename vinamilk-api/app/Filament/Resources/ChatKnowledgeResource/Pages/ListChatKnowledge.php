<?php

namespace App\Filament\Resources\ChatKnowledgeResource\Pages;

use App\Filament\Resources\ChatKnowledgeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChatKnowledge extends ListRecords
{
    protected static string $resource = ChatKnowledgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
