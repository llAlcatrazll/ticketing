<?php

namespace App\Filament\Clusters\Dossiers\Resources\ClosedDossierResource\Pages;

use App\Filament\Actions\NoteDossierAction;
use App\Filament\Clusters\Dossiers\Resources\ClosedDossierResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDossier extends ViewRecord
{
    protected static string $resource = ClosedDossierResource::class;

    public function getHeading(): string
    {
        return str($this->record->name)->limit(36, '...', true);
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(array_slice(parent::getBreadcrumbs(), 0, -1), [
            str($this->record->name)->limit(36, '...', true),
        ]);
    }

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
            Actions\RestoreAction::make()
                ->label('Restore')
                ->modalHeading('Restore dossier'),
            NoteDossierAction::make()
                ->icon(null),
            Actions\EditAction::make()
                ->label('Edit')
                ->hidden($this->record->trashed())
                ->slideOver(),
            Actions\ActionGroup::make([
                Actions\DeleteAction::make()
                    ->modalHeading('Delete dossier'),
                Actions\ForceDeleteAction::make()
                    ->modalHeading('Force delete dossier'),
            ]),
        ];
    }
}
