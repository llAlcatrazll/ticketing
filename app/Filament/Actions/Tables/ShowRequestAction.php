<?php

namespace App\Filament\Actions\Tables;

use App\Filament\Actions\Concerns\ShowRequest;
use Filament\Tables\Actions\ViewAction;

class ShowRequestAction extends ViewAction
{
    use ShowRequest;

    protected function setUp(): void
    {
        $this->bootShowRequest();
    }
}
