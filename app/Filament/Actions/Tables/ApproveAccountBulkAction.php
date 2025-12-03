<?php

namespace App\Filament\Actions\Tables;

use App\Filament\Actions\Concerns\ApproveAccount;
use Filament\Tables\Actions\BulkAction;

class ApproveAccountBulkAction extends BulkAction
{
    use ApproveAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootApproveUser();
    }
}
