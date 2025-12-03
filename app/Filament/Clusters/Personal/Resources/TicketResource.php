<?php

namespace App\Filament\Clusters\Personal\Resources;

use App\Enums\RequestClass;
use App\Filament\Actions\Tables\CancelRequestAction;
use App\Filament\Actions\Tables\CompileRequestAction;
use App\Filament\Actions\Tables\ComplyRequestAction;
use App\Filament\Actions\Tables\RecallRequestAction;
use App\Filament\Actions\Tables\ReopenRequestAction;
use App\Filament\Actions\Tables\ResolveRequestAction;
use App\Filament\Actions\Tables\ResubmitRequestAction;
use App\Filament\Actions\Tables\ShowRequestAction;
use App\Filament\Actions\Tables\TakeFeedbackAction;
use App\Filament\Actions\Tables\UndoRecentAction;
use App\Filament\Actions\Tables\UpdateRequestAction;
use App\Filament\Actions\Tables\ViewRequestHistoryAction;
use App\Filament\Clusters\Personal;
use App\Filament\Clusters\Personal\Resources\RequestResource\Pages\ListTickets;
use App\Filament\Clusters\Personal\Resources\RequestResource\Pages\NewTicket;
use App\Filament\Resources\RequestResource;
use Filament\Tables\Actions\ActionGroup;

class TicketResource extends RequestResource
{
    public static ?bool $inbound = null;

    protected static ?string $cluster = Personal::class;

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
            ComplyRequestAction::make(),
            ResubmitRequestAction::make(),
            ResolveRequestAction::make(),
            ShowRequestAction::make(),
            ViewRequestHistoryAction::make(),
            ActionGroup::make([
                TakeFeedbackAction::make(),
                UndoRecentAction::make(),
                ReopenRequestAction::make(),
                UpdateRequestAction::make(),
                RecallRequestAction::make(),
                CancelRequestAction::make(),
                CompileRequestAction::make(),
            ]),
        ];
    }
}
