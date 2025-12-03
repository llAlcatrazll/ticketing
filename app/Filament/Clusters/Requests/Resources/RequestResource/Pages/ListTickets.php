<?php

namespace App\Filament\Clusters\Requests\Resources\RequestResource\Pages;

use App\Filament\Actions\NewRequestPromptAction;
use App\Filament\Clusters\Requests\Resources\TicketResource;
use App\Filament\Concerns\HasTicketTabs;
use Filament\Resources\Pages\ListRecords;

class ListTickets extends ListRecords
{
    use HasTicketTabs;

    protected static string $resource = TicketResource::class;

    public function getHeaderActions(): array
    {
        return [
            NewRequestPromptAction::make()
                ->class(static::getResource()::$class),
        ];
    }
}
