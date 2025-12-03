<?php

namespace App\Filament\Concerns;

use App\Enums\ActionStatus;
use App\Enums\RequestClass;
use App\Enums\UserRole;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * @mixin \App\Filament\Clusters\Requests\Resources\RequestResource\Pages\ListInquiries
 * @mixin \App\Filament\Clusters\Outbound\Resources\RequestResource\Pages\ListInquiries
 * @mixin \App\Filament\Clusters\Personal\Resources\RequestResource\Pages\ListInquiries
 */
trait HasInquiryTabs
{
    public function getTabs(): array
    {
        $role = Auth::user()->role;

        $inbound = static::getResource()::$inbound;

        $query = fn () => static::getResource()::getEloquentQuery();

        return [
            'all' => Tab::make('All')
                ->icon(RequestClass::INQUIRY->getIcon())
                ->badge(fn () => $query()->count()),
            ...(in_array($role, [UserRole::ADMIN, UserRole::MODERATOR, UserRole::ROOT]) ? [
                $inbound ? 'submitted' : 'received' => Tab::make($inbound ? 'Submitted' : 'Received')
                    ->modifyQueryUsing(fn (Builder $query) => $query->whereRelation('action', 'status', ActionStatus::SUBMITTED))
                    ->icon(! $inbound ? 'gmdi-inbox-o' : ActionStatus::SUBMITTED->getIcon())
                    ->badge(fn () => $query()->whereRelation('action', 'status', ActionStatus::SUBMITTED)->count()),
                'assigned' => Tab::make('Assigned')
                    ->modifyQueryUsing(fn (Builder $query) => $query->whereRelation('action', 'status', ActionStatus::ASSIGNED))
                    ->icon(ActionStatus::ASSIGNED->getIcon())
                    ->badge(fn () => $query()->whereRelation('action', 'status', ActionStatus::ASSIGNED)->count()),
            ] : []),
            ...($role !== UserRole::ROOT ? [
                'pending' => Tab::make('Pending')
                    ->modifyQueryUsing(fn (Builder $query) => $query->whereRelation('action', 'status', ActionStatus::ASSIGNED))
                    ->icon('gmdi-hourglass-empty-o')
                    ->badge(fn () => $query()->whereRelation('action', 'status', ActionStatus::ASSIGNED)->count()),
            ] : []),
            'processing' => Tab::make('Processing')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereRelation('action', 'status', ActionStatus::RESPONDED))
                ->icon(ActionStatus::IN_PROGRESS->getIcon())
                ->badge(fn () => $query()->whereRelation('action', 'status', ActionStatus::RESPONDED)->count()),
            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('action', fn ($query) => $query->whereIn('status', [
                    ActionStatus::COMPLETED,
                    ActionStatus::REOPENED,
                ])))
                ->icon(ActionStatus::COMPLETED->getIcon())
                ->badge(fn () => $query()->whereHas('action', fn ($query) => $query->whereIn('status', [
                    ActionStatus::COMPLETED,
                    ActionStatus::REOPENED,
                ]))->count()),
            'closed' => Tab::make('Closed')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereRelation('action', 'status', ActionStatus::CLOSED))
                ->icon(ActionStatus::CLOSED->getIcon())
                ->badge(fn () => $query()->whereRelation('action', 'status', ActionStatus::CLOSED)->count()),
        ];
    }
}
