<?php

namespace App\Filament\Actions;

use App\Filament\Actions\Concerns\ChangePassword;
use Filament\Actions\Action;

class ChangePasswordAction extends Action
{
    use ChangePassword;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootChangePassword();
    }
}
