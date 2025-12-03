<?php

namespace App\Filament\Clusters\Requests\Resources;

use App\Enums\RequestClass;
use App\Enums\UserRole;
use App\Filament\Actions\Tables\AssignRequestAction;
use App\Filament\Actions\Tables\CloseRequestAction;
use App\Filament\Actions\Tables\CompileRequestAction;
use App\Filament\Actions\Tables\CompleteRequestAction;
use App\Filament\Actions\Tables\DeleteRequestAction;
use App\Filament\Actions\Tables\QueueRequestAction;
use App\Filament\Actions\Tables\RecategorizeRequestAction;
use App\Filament\Actions\Tables\ReclassifyRequestAction;
use App\Filament\Actions\Tables\RejectRequestAction;
use App\Filament\Actions\Tables\RequeueRequestAction;
use App\Filament\Actions\Tables\RestoreRequestAction;
use App\Filament\Actions\Tables\ShowRequestAction;
use App\Filament\Actions\Tables\StartRequestAction;
use App\Filament\Actions\Tables\SuspendRequestAction;
use App\Filament\Actions\Tables\TagRequestAction;
use App\Filament\Actions\Tables\UndoRecentAction;
use App\Filament\Actions\Tables\ViewRequestHistoryAction;
use App\Filament\Clusters\Requests\Resources\RequestResource\Pages\ListTickets;
use App\Filament\Clusters\Requests\Resources\RequestResource\Pages\NewTicket;
use App\Filament\Resources\RequestResource;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ForceDeleteAction;
use Illuminate\Support\Facades\Auth;

class TicketResource extends RequestResource
{
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
        return match (Auth::user()->role) {
            UserRole::ROOT => [
                ShowRequestAction::make()
                    ->hidden(false),
                ViewRequestHistoryAction::make()
                    ->hidden(false),
                ActionGroup::make([
                    CompileRequestAction::make(),
                    RestoreRequestAction::make(),
                    DeleteRequestAction::make(),
                    ForceDeleteAction::make()
                        ->label('Purge'),
                ]),
            ],
            UserRole::ADMIN, UserRole::MODERATOR => [
                StartRequestAction::make(),
                QueueRequestAction::make(),
                AssignRequestAction::make(),
                ShowRequestAction::make(),
                ViewRequestHistoryAction::make(),
                ActionGroup::make([
                    TagRequestAction::make(),
                    CompleteRequestAction::make(),
                    UndoRecentAction::make(),
                    SuspendRequestAction::make(),
                    RequeueRequestAction::make(),
                    RejectRequestAction::make(),
                    CompileRequestAction::make(),
                    RecategorizeRequestAction::make(),
                    ReclassifyRequestAction::make(),
                    CloseRequestAction::make(),
                ]),
            ],
            UserRole::AGENT => [
                StartRequestAction::make(),
                ShowRequestAction::make(),
                ViewRequestHistoryAction::make(),
                ActionGroup::make([
                    TagRequestAction::make(),
                    CompleteRequestAction::make(),
                    UndoRecentAction::make(),
                    SuspendRequestAction::make(),
                    RequeueRequestAction::make(),
                    RejectRequestAction::make(),
                    CompileRequestAction::make(),
                    RecategorizeRequestAction::make(),
                    ReclassifyRequestAction::make(),
                    CloseRequestAction::make(),
                ]),
            ],
            default => [],
        };
    }
}
