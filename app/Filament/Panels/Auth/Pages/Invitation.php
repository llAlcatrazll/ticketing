<?php

namespace App\Filament\Panels\Auth\Pages;

use App\Enums\UserRole;
use App\Filament\Panels\Auth\Concerns\BaseAuthPage;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\Verify;
use App\Models\Organization;
use App\Models\User;
use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class Invitation extends SimplePage implements HasMiddleware
{
    use BaseAuthPage;

    #[Url]
    public string $as = '';

    #[Url]
    public string $at = '';

    #[Url]
    public string $recipient = '';

    #[Url]
    public string $referrer = '';

    #[Url]
    public string $to = '';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $layout = 'filament-panels::components.layout.base';

    protected static string $view = 'filament.panels.auth.pages.invitation';

    protected ?string $maxWidth = 'xl';

    protected ?string $heading = 'Invitation Request';

    public static function getSlug(): string
    {
        return 'invitation-request/prompt';
    }

    public static function getRelativeRouteName(): string
    {
        return 'auth.invitation-request.prompt';
    }

    public static function middleware(): array
    {
        return [
            Authenticate::class,
            Verify::class,
        ];
    }

    public function getSubheading(): string|Htmlable|null
    {
        return match ($this->valid) {
            false => 'The organization invitation request is invalid or expired.',
            default => null,
        };
    }

    #[Computed]
    public function unauthorized()
    {
        return request()->hasValidSignature() &&
            $this->recipient !== Auth::user()->email;
    }

    #[Computed]
    public function valid(): bool
    {
        return request()->hasValidSignature() &&
            $this->role &&
            $this->organization &&
            $this->recipient === Auth::user()->email &&
            $this->to !== Auth::user()->organization_id;
    }

    #[Computed]
    public function role(): UserRole
    {
        return UserRole::tryFrom($this->as);
    }

    #[Computed]
    public function time(): string
    {
        return Carbon::parse((int) $this->at)
            ->format('\o\n jS \o\f F Y \a\t H:i');
    }

    #[Computed]
    public function invitee(): User
    {
        return User::where('email', $this->recipient)
            ->firstOrFail();
    }

    #[Computed]
    public function inviter(): ?User
    {
        return User::firstWhere('email', $this->referrer);
    }

    #[Computed]
    public function organization(): ?Organization
    {
        return Organization::find($this->to);
    }

    public function acceptAction(): Action
    {
        return Action::make('accept')
            ->icon('heroicon-o-check')
            // ->requiresConfirmation()
            // ->modalIcon('heroicon-o-check-badge')
            // ->modalHeading('Accept Invitation')
            // ->modalDescription('You are about to accept the invitation. If you are in under different organization, you will be removed from it.')
            ->hidden($this->recipient !== Auth::user()->email)
            ->action(function () {
                try {
                    DB::transaction(function () {
                        /** @var User $user */
                        $user = Auth::user();

                        $user->approved_by = $this->inviter->id;
                        $user->organization_id = $this->to;
                        $user->approved_at = $this->at;
                        $user->role = $this->as;

                        $user->save();
                    });

                    Notification::make()
                        ->title('Invitation accepted')
                        ->success()
                        ->send();

                    return redirect()->route("filament.{$this->as}.pages.dashboard");
                } catch (Exception $ex) {
                    Notification::make()
                        ->title('Something went wrong')
                        ->danger()
                        ->send();
                }
            });
    }

    public function homeAction(): Action
    {
        return Action::make('home')
            ->icon('heroicon-o-home')
            ->url('/');
    }
}
