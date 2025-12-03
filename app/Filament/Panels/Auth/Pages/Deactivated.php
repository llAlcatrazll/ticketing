<?php

namespace App\Filament\Panels\Auth\Pages;

use App\Filament\Panels\Auth\Concerns\BaseAuthPage;
use App\Http\Middleware\Approve;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\Verify;
use App\Http\Responses\LoginResponse;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Pages\SimplePage;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Routing\Controllers\HasMiddleware;

class Deactivated extends SimplePage implements HasMiddleware
{
    use BaseAuthPage;

    public ?User $user;

    protected static string $layout = 'filament-panels::components.layout.base';

    protected static string $view = 'filament.panels.auth.pages.deactivated';

    public static function getSlug(): string
    {
        return 'deactivated-access/prompt';
    }

    public static function getRelativeRouteName(): string
    {
        return 'auth.deactivated-access.prompt';
    }

    public static function middleware(): array
    {
        return [
            Authenticate::class,
            Verify::class,
            Approve::class,
        ];
    }

    public function mount()
    {
        $this->user = Filament::auth()->user();

        if ($this->user->hasActiveAccess()) {
            (new LoginResponse)->toResponse(request());
        }
    }

    public function getTitle(): string|Htmlable
    {
        return 'User access terminated';
    }
}
