<?php

namespace App\Filament\Clusters\Personal\Resources\RequestResource\Pages;

use App\Filament\Actions\NewRequestPromptAction;
use App\Filament\Clusters\Personal\Resources\SuggestionResource;
use App\Filament\Concerns\HasSuggestionTabs;
use Filament\Resources\Pages\ListRecords;

class ListSuggestions extends ListRecords
{
    use HasSuggestionTabs;

    protected static string $resource = SuggestionResource::class;

    public function getHeaderActions(): array
    {
        return [
            NewRequestPromptAction::make()
                ->class(static::getResource()::$class),
        ];
    }
}
