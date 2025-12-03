<?php

namespace App\Filament\Actions\Tables;

use App\Filament\Actions\Concerns\ApproveAccount;
use Filament\Tables\Actions\Action;

class ApproveAccountAction extends Action
{
    use ApproveAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootApproveUser();
    }
}
