<?php

namespace App\Filament\Clusters\Dossiers\Resources\ClosedDossierResource\Pages;

use App\Filament\Clusters\Dossiers\Resources\ClosedDossierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListClosedDossiers extends ListRecords
{
    protected static string $resource = ClosedDossierResource::class;

    public function getTitle(): string|Htmlable
    {
        return static::$resource::getLabel();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New dossier')
                ->modalHeading('Create new dossier')
                ->createAnother(false)
                ->slideOver(),
        ];
    }
}
