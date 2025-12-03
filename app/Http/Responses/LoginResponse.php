<?php

namespace App\Http\Responses;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements Responsable
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        /** @var User $user */
        $user = $request->user();

        $route = match ($user->role) {
            UserRole::ROOT => 'filament.root.pages.dashboard',
            UserRole::ADMIN => 'filament.admin.pages.dashboard',
            UserRole::MODERATOR => 'filament.moderator.pages.dashboard',
            UserRole::AGENT => 'filament.agent.pages.dashboard',
            UserRole::USER => 'filament.user.pages.dashboard',
            UserRole::AUDITOR => 'filament.auditor.pages.dashboard',
            default => 'filament.home.pages.',
        };

        return redirect()->route($route);
    }
}
