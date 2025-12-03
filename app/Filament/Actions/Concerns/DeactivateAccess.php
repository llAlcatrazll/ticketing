<?php

namespace App\Filament\Actions\Concerns;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;

trait DeactivateAccess
{
    protected function bootDeactivateUser(): void
    {
        $this->name('deactivate-user');

        $this->requiresConfirmation();

        $this->visible(fn (User $user) => $user->hasVerifiedEmail() && $user->hasApprovedAccount() && ! $user->trashed());

        $this->label(fn (User $user) => $user->deactivated_at ? 'Reactivate access' : 'Deactivate access');

        $this->icon(fn (User $user) => $user->deactivated_at ? 'gmdi-verified-user-o' : 'gmdi-block-o');

        $this->color(fn (User $user) => $user->deactivated_at ? 'success' : 'warning');

        $this->modalIcon(fn (User $user) => $user->deactivated_at ? 'gmdi-verified-user-o' : 'gmdi-block-o');

        $this->modalSubmitActionLabel(fn (User $user) => $user->deactivated_at ? 'Reactivate' : 'Confirm');

        $this->successNotificationTitle(fn (User $user) => $user->deactivated_at ? 'User deactivated' : 'User reactivated');

        $this->form([
            TextInput::make('password')
                ->rule('required')
                ->markAsRequired()
                ->password()
                ->currentPassword()
                ->helperText('Enter your password to confirm this action.'),
        ]);

        $this->action(function (User $user) {
            match (true) {
                isset($user->deactivated_at) => $user->forceFill([
                    'deactivated_at' => null,
                    'deactivated_by' => null,
                ]),
                default => $user->forceFill([
                    'deactivated_at' => now(),
                    'deactivated_by' => Auth::id(),
                ]),
            };

            $user->save();

            $this->sendSuccessNotification();
        });

        $this->modalDescription(function (User $user) {
            if (is_null($user->deactivated_at)) {
                return 'Deactivate this user to revoke their access.';
            }

            $label = <<<HTML
                <span class="text-sm text-custom-600 dark:text-custom-400" style="--c-400:var(--warning-400);--c-600:var(--warning-600);">Warning !</span> <br>
                This user has been deactivated by
                <br> {$user->deactivatedBy?->name} ({$user->deactivatedBy?->email}) on {$user->deactivated_at->format('jS \o\f F \a\t H:i:s')}.
            HTML;

            return str($label)->toHtmlString();
        });
    }
}
