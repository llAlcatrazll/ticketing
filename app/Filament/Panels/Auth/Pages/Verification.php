<?php

namespace App\Filament\Panels\Auth\Pages;

use App\Filament\Panels\Auth\Concerns\BaseAuthPage;
use App\Http\Middleware\Authenticate;
use App\Http\Responses\LoginResponse;
use Filament\Facades\Filament;
use Filament\Pages\Auth\EmailVerification\EmailVerificationPrompt;
use Illuminate\Routing\Controllers\HasMiddleware;

class Verification extends EmailVerificationPrompt implements HasMiddleware
{
    use BaseAuthPage;

    protected static string $layout = 'filament-panels::components.layout.base';

    protected static string $view = 'filament.panels.auth.pages.verification';

    public static function getSlug(): string
    {
        return 'email-verification/prompt';
    }

    public static function getRelativeRouteName(): string
    {
        return 'auth.email-verification.prompt';
    }

    public static function middleware(): array
    {
        return [
            Authenticate::class,
        ];
    }

    public function mount(): void
    {
        /** @var User */
        $user = Filament::auth()->user();

        if ($user->hasVerifiedEmail()) {
            (new LoginResponse)->toResponse(request());
        }
    }
}
