<?php

namespace App\Filament\Actions\Concerns;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\Alignment;

trait ChangePassword
{
    protected function bootChangePassword(): void
    {
        $this->name('change-password');

        $this->icon('gmdi-lock');

        $this->visible(fn (User $user) => $user->hasVerifiedEmail() && $user->hasApprovedAccount() && $user->hasActiveAccess() && ! $user->trashed());

        $this->modalIcon('gmdi-lock');

        $this->modalSubmitActionLabel('Change Password');

        $this->modalWidth('md');

        $this->modalFooterActionsAlignment(Alignment::Justify);

        $this->successNotificationTitle('Password changed');

        $this->form([
            TextInput::make('password')
                ->rule('required')
                ->markAsRequired()
                ->password()
                ->helperText('Enter new password.'),
        ]);

        $this->action(function (User $user, array $data) {
            $user->forceFill(['password' => $data['password']])->save();

            $this->sendSuccessNotification();
        });

        $this->modalDescription('Change this account\'s password.');
    }
}
