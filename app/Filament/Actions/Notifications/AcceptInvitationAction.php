<?php

namespace App\Filament\Actions\Notifications;

use App\Models\User;
use Filament\Notifications\Actions\Action;

class AcceptInvitationAction extends Action
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('accept-invitation');

        $this->markAsUnread();
    }

    public function for(User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
