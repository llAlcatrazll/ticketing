<?php

namespace App\Filament\Clusters\Management\Resources\UserResource\Pages;

use App\Filament\Actions\InviteUserAction;
use App\Filament\Clusters\Management\Resources\UserResource;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            InviteUserAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $panel = Filament::getCurrentPanel()->getId();

        $query = fn () => static::$resource::getEloquentQuery();

        return [
            'all' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->verifiedEmail()->approvedAccount()->withoutDeactivated()->withoutTrashed())
                ->icon('gmdi-verified-o')
                ->badge(fn () => $query()->verifiedEmail()->approvedAccount()->withoutDeactivated()->withoutTrashed()->count()),
            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->forApproval())
                ->icon('gmdi-hourglass-empty')
                ->badge(fn () => $query()->forApproval()->withoutDeactivated()->withoutTrashed()->count()),
            'unverified' => Tab::make('Unverified')
                ->modifyQueryUsing(fn (Builder $query) => $query->forVerification())
                ->icon('gmdi-mark-email-unread-o')
                ->badge(fn () => $query()->forVerification()->withoutDeactivated()->withoutTrashed()->count()),
            'deactivated' => Tab::make('Deactivated')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyDeactivated()->withoutTrashed())
                ->icon('gmdi-gpp-bad-o')
                ->badgeColor('danger')
                ->badge(fn () => $query()->onlyDeactivated()->withoutTrashed()->count()),
            ...($panel === 'root' ? [
                'trashed' => Tab::make('Trashed')
                    ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                    ->icon('gmdi-delete-o')
                    ->badgeColor('danger')
                    ->badge(fn () => $query()->onlyTrashed()->count()),
            ] : []),
        ];
    }
}
