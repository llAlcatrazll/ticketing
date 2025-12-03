<?php

namespace App\Filament\Actions\Tables;

use App\Filament\Actions\Concerns\UpdateRequest;
use Filament\Tables\Actions\EditAction;

class UpdateRequestAction extends EditAction
{
    use UpdateRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootUpdateRequest();
    }
}
