<?php

namespace App\Filament\Clusters\Dossiers\Resources\AllDossierResource\Pages;

use App\Filament\Clusters\Dossiers\Resources\AllDossierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\Support\Htmlable;

class ListDossiers extends ListRecords
{
    protected static string $resource = AllDossierResource::class;

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
                ->slideOver()
                ->modalWidth(MaxWidth::ExtraLarge),
        ];
    }
}
