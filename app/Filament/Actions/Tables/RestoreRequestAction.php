<?php

namespace App\Filament\Actions\Tables;

use App\Filament\Actions\Concerns\RestoreRequest;
use Filament\Tables\Actions\RestoreAction;

class RestoreRequestAction extends RestoreAction
{
    use RestoreRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootRestoreRequest();
    }
}
