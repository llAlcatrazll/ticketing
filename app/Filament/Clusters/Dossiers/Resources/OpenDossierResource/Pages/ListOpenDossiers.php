<?php

namespace App\Filament\Clusters\Dossiers\Resources\OpenDossierResource\Pages;

use App\Filament\Clusters\Dossiers\Resources\OpenDossierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListOpenDossiers extends ListRecords
{
    protected static string $resource = OpenDossierResource::class;

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
