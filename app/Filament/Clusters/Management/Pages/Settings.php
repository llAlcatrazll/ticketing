<?php

namespace App\Filament\Clusters\Management\Pages;

use App\Filament\Clusters\Management;
use App\Models\Organization;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Settings extends Page
{
    use InteractsWithFormActions;

    public array $data = [];

    protected static ?int $navigationSort = PHP_INT_MAX;

    protected static ?string $navigationIcon = 'gmdi-settings-o';

    protected static string $view = 'filament.panels.admin.clusters.organization.pages.settings';

    protected static ?string $cluster = Management::class;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()->getId() !== 'root';
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);

        $this->fillForm();
    }

    public function getBreadcrumbs(): array
    {
        if (filled($cluster = static::getCluster())) {
            return $cluster::unshiftClusterBreadcrumbs([
                Settings::getUrl() => static::getTitle(),
            ]);
        }

        return [];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make()
                    ->contained(false)
                    ->columns(3)
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Organization')
                            ->icon('gmdi-domain-o')
                            ->schema([
                                Forms\Components\FileUpload::make('logo')
                                    ->avatar()
                                    ->alignCenter()
                                    ->directory('logos'),
                                Forms\Components\Group::make()
                                    ->columnSpan([
                                        'md' => 2,
                                    ])
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->unique(ignoreRecord: true)
                                            ->markAsRequired()
                                            ->rule('required'),
                                        Forms\Components\TextInput::make('code')
                                            ->markAsRequired()
                                            ->rule('required')
                                            ->unique(ignoreRecord: true),
                                    ]),
                                Forms\Components\TextInput::make('address')
                                    ->maxLength(255)
                                    ->columnSpan([
                                        'sm' => 1,
                                        'md' => 3,
                                    ]),
                                Forms\Components\TextInput::make('building')
                                    ->maxLength(255)
                                    ->columnSpan([
                                        'sm' => 1,
                                        'md' => 2,
                                    ]),
                                Forms\Components\TextInput::make('room')
                                    ->columnSpan(1),
                            ]),
                        Forms\Components\Tabs\Tab::make('Configuration')
                            ->icon('gmdi-build-circle-o')
                            ->schema([
                                Forms\Components\TextInput::make('settings.auto_queue')
                                    ->label('Request auto queue')
                                    ->placeholder('Number of minutes')
                                    ->helperText('Number of minutes to auto queue a request')
                                    ->rules(['numeric']),
                                Forms\Components\TextInput::make('settings.auto_resolve')
                                    ->label('Request auto resolve')
                                    ->placeholder('Number of hours')
                                    ->helperText('Number of hours to auto resolve a completed request')
                                    ->minValue(48)
                                    ->rules(['numeric']),
                                Forms\Components\TextInput::make('settings.auto_assign')
                                    ->label('Request auto assign')
                                    ->placeholder('Number of minutes')
                                    ->helperText('Number of minutes to auto assign a request')
                                    ->rules(['numeric']),
                                // Forms\Components\Toggle::make('settings.support_reassignment')
                                //     ->inline(false)
                                //     ->disabled(),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function update(): void
    {
        $data = $this->form->getState();

        $organization = Organization::find(Auth::user()->organization_id);

        DB::transaction(function () use ($data, $organization) {
            $organization->update($data);

            Notification::make()
                ->success()
                ->title('Settings updated')
                ->send();
        });
    }

    protected function fillForm(): void
    {
        $this->form->fill(Organization::find(Auth::user()->organization_id)->toArray());
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('Update')
                ->submit('update')
                ->keyBindings(['mod+s']),
        ];
    }
}
