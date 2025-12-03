<?php

namespace App\Filament\Clusters\Management\Resources\CategoryResource\Pages;

use App\Filament\Actions\ViewQrCodeAction;
use App\Filament\Clusters\Management\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Builder;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->createAnother(false)
                ->slideOver()
                ->modalWidth(MaxWidth::Large),
            ViewQrCodeAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $query = fn () => static::$resource::getEloquentQuery();

        return [
            'all' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed())
                ->icon('gmdi-verified-o')
                ->badge(fn () => $query()->withoutTrashed()->count()),
            'trashed' => Tab::make('Trashed')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->icon('gmdi-delete-o')
                ->badgeColor('danger')
                ->badge(fn () => $query()->onlyTrashed()->count()),
        ];
    }
}
