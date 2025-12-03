<?php

namespace App\Providers\Filament;

use App\Filament\Panels\Auth\Controllers\EmailVerificationController;
use App\Filament\Panels\Auth\Pages\Approval;
use App\Filament\Panels\Auth\Pages\Deactivated;
use App\Filament\Panels\Auth\Pages\Initialization;
use App\Filament\Panels\Auth\Pages\Invitation;
use App\Filament\Panels\Auth\Pages\Login;
use App\Filament\Panels\Auth\Pages\Redirect;
use App\Filament\Panels\Auth\Pages\Registration;
use App\Filament\Panels\Auth\Pages\Verification;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AuthPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('auth')
            ->path('auth')
            ->homeUrl('/')
            ->brandLogo(fn () => view('banner'))
            ->font('Urbanist')
            ->login(Login::class)
            ->registration(Registration::class)
            ->passwordReset()
            ->revealablePasswords(false)
            ->colors([...Color::all(), 'gray' => Color::Neutral])
            ->discoverResources(in: app_path('Filament/Panels/Auth/Resources'), for: 'App\\Filament\\Panels\\Auth\\Resources')
            ->discoverPages(in: app_path('Filament/Panels/Auth/Pages'), for: 'App\\Filament\\Panels\\Auth\\Pages')
            ->discoverClusters(in: app_path('Filament/Panels/Auth/Clusters'), for: 'App\\Filament\\Panels\\Auth\\Clusters')
            ->pages([
                Redirect::class,
                Verification::class,
                Approval::class,
                Initialization::class,
                Invitation::class,
                Deactivated::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->maxContentWidth(MaxWidth::ScreenTwoExtraLarge)
            ->databaseTransactions()
            ->topNavigation()
            ->spa();
    }

    public function boot(): void
    {
        Route::middleware('web')->group(
            fn () => Route::get('/auth/email-verification/verify/{id}/{hash}', EmailVerificationController::class)
                ->name('filament.auth.auth.email-verification.verify')
        );
    }
}
