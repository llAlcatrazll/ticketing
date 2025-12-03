<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;

class InviteUserAction extends Action
{
    use Concerns\InviteUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootInviteUser();
    }
}
