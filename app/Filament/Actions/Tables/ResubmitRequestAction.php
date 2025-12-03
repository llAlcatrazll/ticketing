<?php

namespace App\Filament\Actions\Tables;

use App\Filament\Actions\Concerns\ResubmitRequest;
use Filament\Tables\Actions\Action;

class ResubmitRequestAction extends Action
{
    use ResubmitRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootResubmitRequest();
    }
}
