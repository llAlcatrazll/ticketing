<?php

namespace App\Filament\Actions;

use App\Filament\Actions\Concerns\NoteDossier;
use Filament\Actions\Action;

class NoteDossierAction extends Action
{
    use NoteDossier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootTraitDossierAction();
    }
}
