<?php

namespace App\Filament\Clusters\Management\Resources\OrganizationResource\Pages;

use App\Filament\Clusters\Management\Resources\OrganizationResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateOrganization extends CreateRecord
{
    protected static string $resource = OrganizationResource::class;

    protected static bool $canCreateAnother = false;

    public function getSubNavigation(): array
    {
        if (filled($cluster = static::getCluster())) {
            return $this->generateNavigationItems($cluster::getClusteredComponents());
        }

        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
                ->url(static::$resource::getUrl()),
        ];
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->hidden();
    }
}
