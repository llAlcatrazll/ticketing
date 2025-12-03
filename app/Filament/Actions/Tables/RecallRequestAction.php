<?php

namespace App\Filament\Actions\Tables;

use App\Filament\Actions\Concerns\RecallRequest;
use Filament\Tables\Actions\Action;

class RecallRequestAction extends Action
{
    use RecallRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootRecallRequest();
    }
}
