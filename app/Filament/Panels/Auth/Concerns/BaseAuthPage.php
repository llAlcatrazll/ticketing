<?php

namespace App\Filament\Panels\Auth\Concerns;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Concerns\HasRoutes;
use Filament\Pages\Concerns\InteractsWithFormActions;

trait BaseAuthPage
{
    use HasRoutes, InteractsWithFormActions, InteractsWithForms;

    public function logoutAction(): Action
    {
        return Action::make('logout')
            ->outlined()
            ->icon('gmdi-logout-o')
            ->action(function () {
                Filament::auth()->logout();

                session()->invalidate();
                session()->regenerateToken();

                return redirect('/');
            });
    }

    public static function registerNavigationItems(): null
    {
        return null;
    }
}
