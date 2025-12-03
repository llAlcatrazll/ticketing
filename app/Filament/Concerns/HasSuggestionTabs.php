<?php

namespace App\Filament\Concerns;

use App\Enums\ActionStatus;
use App\Enums\RequestClass;
use Filament\Resources\Components\Tab;

/**
 * @mixin \App\Filament\Clusters\Requests\Resources\RequestResource\Pages\ListInquiries
 * @mixin \App\Filament\Clusters\Outbound\Resources\RequestResource\Pages\ListInquiries
 * @mixin \App\Filament\Clusters\Personal\Resources\RequestResource\Pages\ListInquiries
 */
trait HasSuggestionTabs
{
    public function getTabs(): array
    {
        $query = fn () => static::getResource()::getEloquentQuery();

        return [
            'all' => Tab::make('All')
                ->icon(RequestClass::SUGGESTION->getIcon())
                ->badge(fn () => $query()->count()),
            'open' => Tab::make('Open')
                ->modifyQueryUsing(fn ($query) => $query->whereRelation('action', 'status', '!=', ActionStatus::CLOSED))
                ->icon('gmdi-circle-o')
                ->badge(fn () => $query()->whereRelation('action', 'status', '!=', ActionStatus::CLOSED)->count()),
            'closed' => Tab::make('Closed')
                ->modifyQueryUsing(fn ($query) => $query->whereRelation('action', 'status', ActionStatus::CLOSED))
                ->icon(ActionStatus::CLOSED->getIcon())
                ->badge(fn () => $query()->whereRelation('action', 'status', ActionStatus::CLOSED)->count()),
        ];
    }
}
