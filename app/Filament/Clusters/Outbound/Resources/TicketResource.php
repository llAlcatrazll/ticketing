<?php

namespace App\Filament\Clusters\Outbound\Resources;

use App\Enums\RequestClass;
use App\Filament\Actions\Tables\ShowRequestAction;
use App\Filament\Actions\Tables\ViewRequestHistoryAction;
use App\Filament\Clusters\Outbound;
use App\Filament\Clusters\Outbound\Resources\RequestResource\Pages\ListTickets;
use App\Filament\Clusters\Outbound\Resources\RequestResource\Pages\NewTicket;
use App\Filament\Resources\RequestResource;

class TicketResource extends RequestResource
{
    public static ?bool $inbound = false;

    protected static ?string $cluster = Outbound::class;

    public static ?RequestClass $class = RequestClass::TICKET;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?int $navigationSort = -2;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $label = 'Tickets';

    public static function getPages(): array
    {
        return [
            'index' => ListTickets::route('/'),
            'new' => NewTicket::route('new/{record}'),
        ];
    }

    public static function tableActions(): array
    {
        return [
            ShowRequestAction::make(),
            ViewRequestHistoryAction::make(),
        ];
    }
}
