<?php

namespace App\Filament\Actions;

use App\Filament\Actions\Concerns\DeactivateAccess;
use Filament\Actions\Action;

class DeactivateAccessAction extends Action
{
    use DeactivateAccess;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootDeactivateUser();
    }
}
