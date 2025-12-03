<?php

namespace App\Filament\Clusters\Requests\Resources\RequestResource\Pages;

use App\Enums\RequestClass;
use App\Filament\Clusters\Requests\Resources\TicketResource;
use App\Filament\Concerns\NewRequest;
use Filament\Resources\Pages\EditRecord;

class NewTicket extends EditRecord
{
    use NewRequest;

    public static RequestClass $classification = RequestClass::TICKET;

    protected static string $resource = TicketResource::class;

    protected static ?string $breadcrumb = 'New Ticket';

    public function getSubNavigation(): array
    {
        if (filled($cluster = static::getCluster())) {
            return $this->generateNavigationItems($cluster::getClusteredComponents());
        }

        return [];
    }
}
