<?php

namespace App\Filament\Concerns;

use App\Enums\ActionStatus;
use App\Enums\RequestClass;
use Filament\Facades\Filament;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * @mixin \App\Filament\Clusters\Requests\Resources\RequestResource\Pages\ListInquiries
 * @mixin \App\Filament\Clusters\Outbound\Resources\RequestResource\Pages\ListInquiries
 * @mixin \App\Filament\Clusters\Personal\Resources\RequestResource\Pages\ListInquiries
 */
trait HasTicketTabs
{
    public function getTabs(): array
    {
        $panel = Filament::getCurrentPanel()->getId();

        $inbound = static::getResource()::$inbound;

        $query = fn () => static::getResource()::getEloquentQuery();

        return [
            'all' => Tab::make('All')
                ->icon(RequestClass::TICKET->getIcon())
                ->badge(fn () => $query()->count()),
            ...(in_array($panel, ['admin', 'moderator', 'root']) ? [
                $inbound ? 'received' : 'submitted' => Tab::make($inbound ? 'Received' : 'Submitted')
                    ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('action', fn ($query) => $query->whereIn('status', [
                        ActionStatus::SUBMITTED,
                        ActionStatus::QUEUED,
                    ])))
                    ->icon($inbound ? 'gmdi-inbox-o' : ActionStatus::SUBMITTED->getIcon())
                    ->badge(fn () => $query()->whereHas('action', fn ($query) => $query->whereIn('status', [
                        ActionStatus::SUBMITTED,
                        ActionStatus::QUEUED,
                    ]))->count()),
            ] : []),
            ...(in_array($panel, ['admin', 'moderator', 'root']) ? [
                'assigned' => Tab::make('Assigned')
                    ->modifyQueryUsing(fn (Builder $query) => $query->whereRelation('action', 'status', ActionStatus::ASSIGNED))
                    ->icon(ActionStatus::ASSIGNED->getIcon())
                    ->badge(fn () => $query()->whereRelation('action', 'status', ActionStatus::ASSIGNED)->count()),
            ] : []),
            ...($panel !== 'root' && $inbound ? [
                'pending' => Tab::make('Pending')
                    ->modifyQueryUsing(fn (Builder $query) => $query
                        ->whereHas('assignees', fn ($query) => $query->where('assigned_id', Auth::id()))
                        ->whereRelation('action', 'status', ActionStatus::ASSIGNED)
                    )
                    ->icon('gmdi-hourglass-empty-o')
                    ->badge(fn () => $query()
                        ->whereHas('assignees', fn ($query) => $query->where('assigned_id', Auth::id()))
                        ->whereRelation('action', 'status', ActionStatus::ASSIGNED)->count()
                    ),
            ] : []),
            'processing' => Tab::make('Processing')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereRelation('action', 'status', ActionStatus::STARTED))
                ->icon(ActionStatus::IN_PROGRESS->getIcon())
                ->badge(fn () => $query()->whereRelation('action', 'status', ActionStatus::STARTED)->count()),
            'suspended' => Tab::make('Suspended')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('action', fn ($query) => $query->whereIn('status', [
                    ActionStatus::SUSPENDED,
                    ActionStatus::COMPLIED,
                ])))
                ->icon(ActionStatus::SUSPENDED->getIcon())
                ->badge(fn () => $query()->whereHas('action', fn ($query) => $query->whereIn('status', [
                    ActionStatus::SUSPENDED,
                    ActionStatus::COMPLIED,
                ]))->count()),
            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('action', fn ($query) => $query->whereIn('status', [
                    ActionStatus::COMPLETED,
                ])))
                ->icon(ActionStatus::COMPLETED->getIcon())
                ->badge(fn () => $query()->whereHas('action', fn ($query) => $query->whereIn('status', [
                    ActionStatus::COMPLETED,
                ]))->count()),
            'reopened' => Tab::make('Reopened')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('action', fn ($query) => $query->whereIn('status', [
                    ActionStatus::REOPENED,
                ])))
                ->icon(ActionStatus::REOPENED->getIcon())
                ->badge(fn () => $query()->whereHas('action', fn ($query) => $query->whereIn('status', [
                    ActionStatus::REOPENED,
                ]))->count()),
            'closed' => Tab::make('Closed')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereRelation('action', 'status', ActionStatus::CLOSED))
                ->icon(ActionStatus::CLOSED->getIcon())
                ->badge(fn () => $query()->whereRelation('action', 'status', ActionStatus::CLOSED)->count()),
        ];
    }
}
