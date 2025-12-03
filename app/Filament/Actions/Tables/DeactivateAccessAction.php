<?php

namespace App\Filament\Actions\Tables;

use App\Filament\Actions\Concerns\DeactivateAccess;
use Filament\Tables\Actions\Action;

class DeactivateAccessAction extends Action
{
    use DeactivateAccess;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootDeactivateUser();
    }
}
