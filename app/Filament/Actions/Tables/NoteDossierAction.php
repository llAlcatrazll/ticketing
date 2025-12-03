<?php

namespace App\Filament\Actions\Tables;

use App\Filament\Actions\Concerns\NoteDossier;
use Filament\Tables\Actions\Action;

class NoteDossierAction extends Action
{
    use NoteDossier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootTraitDossierAction();
    }
}
