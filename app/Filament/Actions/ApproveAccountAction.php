<?php

namespace App\Filament\Actions;

use App\Filament\Actions\Concerns\ApproveAccount;
use Filament\Actions\Action;

class ApproveAccountAction extends Action
{
    use ApproveAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootApproveUser();
    }
}
