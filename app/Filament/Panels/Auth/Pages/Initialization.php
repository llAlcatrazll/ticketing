<?php

namespace App\Filament\Panels\Auth\Pages;

use App\Filament\Panels\Admin\Clusters\Organization\Resources\UserResource;
use App\Filament\Panels\Auth\Concerns\BaseAuthPage;
use App\Http\Middleware\Active;
use App\Http\Middleware\Approve;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\Verify;
use App\Http\Responses\LoginResponse;
use App\Models\Organization;
use App\Models\User;
use Exception;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class Initialization extends SimplePage implements HasMiddleware
{
    use BaseAuthPage;

    public array $data = [];

    protected static string $layout = 'filament-panels::components.layout.base';

    protected static string $view = 'filament.panels.auth.pages.initialization';

    public static function getSlug(): string
    {
        return 'organization-initialization/prompt';
    }

    public static function getRelativeRouteName(): string
    {
        return 'auth.organization-initialization.prompt';
    }

    public static function middleware(): array
    {
        return [
            Authenticate::class,
            Verify::class,
            Approve::class,
            Active::class,
        ];
    }

    public function mount(): void
    {
        /** @var User */
        $user = Filament::auth()->user();

        if ($user->organization?->exists) {
            (new LoginResponse)->toResponse(request());
        }
    }

    public function getTitle(): string|Htmlable
    {
        /** @var User */
        $user = Auth::user();

        return match (true) {
            isset($user->organization_id) && is_null($user->organization) => 'Organization disabled',
            $user->admin => 'Setup your organization',
            default => 'No organization',
        };
    }

    public function getSubheading(): string|Htmlable|null
    {
        /** @var User */
        $user = Auth::user();

        return match (true) {
            isset($user->organization_id) && is_null($user->organization) => 'Your organization has been disabled. If you believe this is a mistake, please contact support.',
            $user->admin => 'You need to setup your organization first before anyone else can use the system.',
            default => 'You must be invited to an organization before you can use the system.',
        };
    }

    public function getMaxWidth(): MaxWidth|string|null
    {
        return MaxWidth::ExtraLarge;
    }

    public function form(Form $form): Form
    {
        /** @var User */
        $user = Auth::user();

        if (! $user->admin || isset($user->organization_id) && is_null($user->organization)) {
            return $form;
        }

        $next = <<<'JS'
            $wire.dispatchFormEvent(
                'wizard::nextStep',
                'data',
                getStepIndex(step),
            )
        JS;

        return $form
            ->statePath('data')
            ->schema([
                Wizard::make()
                    ->visible()
                    ->contained(false)
                    ->submitAction(new HtmlString(Blade::render('<x-filament::button type="submit">Setup</x-filament::button>')))
                    ->schema([
                        Wizard\Step::make('Name')
                            ->schema([
                                TextInput::make('name')
                                    ->extraAttributes(['onkeydown' => "return event.key != 'Enter';"])
                                    ->extraAlpineAttributes(['@keyup.enter' => $next])
                                    ->unique(Organization::class, 'name')
                                    ->markAsRequired()
                                    ->rule('required'),
                                TextInput::make('code')
                                    ->extraAttributes(['onkeydown' => "return event.key != 'Enter';"])
                                    ->extraAlpineAttributes(['@keyup.enter' => $next])
                                    ->unique(Organization::class, 'code')
                                    ->markAsRequired()
                                    ->rule('required'),
                            ]),
                        Wizard\Step::make('Address')
                            ->schema([
                                TextInput::make('address')
                                    ->extraAttributes(['onkeydown' => "return event.key != 'Enter';"])
                                    ->extraAlpineAttributes(['@keyup.enter' => $next]),
                                TextInput::make('building')
                                    ->extraAttributes(['onkeydown' => "return event.key != 'Enter';"])
                                    ->extraAlpineAttributes(['@keyup.enter' => $next]),
                                TextInput::make('room')
                                    ->extraAttributes(['onkeydown' => "return event.key != 'Enter';"])
                                    ->extraAlpineAttributes(['@keyup.enter' => $next]),
                            ]),
                        Wizard\Step::make('Logo')
                            ->schema([
                                FileUpload::make('logo')
                                    ->hiddenLabel()
                                    ->avatar()
                                    ->alignCenter()
                                    ->directory('logos'),
                            ]),
                    ]),
            ]);
    }

    public function initialize()
    {
        if (! Auth::user()->admin) {
            return;
        }

        try {
            DB::beginTransaction();

            $data = $this->form->getState();

            /** @var User $user */
            $user = Auth::user();

            /** @var Organization $organization */
            $organization = $user->organization()->create($data);

            $user->organization()->associate($organization)->save();

            Notification::make()
                ->success()
                ->title('Success')
                ->send();

            DB::commit();

            return redirect(UserResource::getUrl(panel: 'admin'));
        } catch (Exception $ex) {
            DB::rollBack();

            throw $ex;
            Notification::make()
                ->danger()
                ->title('Failed')
                ->body('Failed to setup your organization')
                ->send();
        }
    }
}
