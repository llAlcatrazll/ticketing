<?php

namespace App\Filament\Actions\Tables;

use App\Filament\Actions\Concerns\DeleteRequest;
use Filament\Tables\Actions\DeleteAction;

class DeleteRequestAction extends DeleteAction
{
    use DeleteRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootDeleteRequest();
    }
}
