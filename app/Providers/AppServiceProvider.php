<?php

namespace App\Providers;

use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Livewire\Notifications;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        }

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        $this->configureFilament();
    }

    protected function configureFilament(): void
    {
        FilamentView::registerRenderHook(PanelsRenderHook::HEAD_START, fn () => Blade::render('@vite(\'resources/css/app.css\')'));

        FilamentView::registerRenderHook(PanelsRenderHook::TOPBAR_START, fn () => Blade::render('<div class="flex items-center w-full h-16 px-4 mx-auto md:px-6 lg:px-8 gap-x-4 max-w-screen-2xl 2xl:px-8">'));

        FilamentView::registerRenderHook(PanelsRenderHook::TOPBAR_END, fn () => Blade::render('</div>'));

        FilamentView::registerRenderHook(PanelsRenderHook::HEAD_START, fn () => Blade::render('<style>.fi-topbar>nav{padding:0!important;}</style>'));

        Notifications::verticalAlignment(VerticalAlignment::End);

        Notifications::alignment(Alignment::Start);

        Table::configureUsing(function (Table $table) {
            $table->paginated([5, 10, 15, 20, 25, 50, 100])
                ->defaultPaginationPageOption(20)
                ->recordClasses(function (Model $model) {
                    if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                        return $model->trashed() ? 'bg-gray-100 dark:bg-gray-800' : null;
                    }
                });
        });

        MarkdownEditor::configureUsing(function (MarkdownEditor $markdownEditor) {
            $markdownEditor->disableToolbarButtons(['attachFiles', 'codeBlock'])
                ->enableToolbarButtons(['h2'])
                ->hintAction(
                    Action::make('preview')
                        ->visible(fn () => ! $markdownEditor->isDisabled())
                        ->modalHeading(fn (MarkdownEditor $component) => "Preview {$component->getLabel()}")
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->modalFooterActionsAlignment(Alignment::End)
                        ->modalWidth(MaxWidth::ExtraLarge)
                        ->infolist(fn ($state) => [
                            TextEntry::make('preview')
                                ->hiddenLabel()
                                ->state(str($state ?? '')->markdown()->toHtmlString())
                                ->markdown(),
                        ]),
                );
        });

        TextInput::configureUsing(fn (TextInput $input) => $input->maxLength(255));

        Select::configureUsing(fn (Select $select) => $select->native(false));

        SelectFilter::configureUsing(fn (SelectFilter $filter) => $filter->native(false));

        TrashedFilter::configureUsing(fn (TrashedFilter $filter) => $filter->native(false));

        $forceDeletes = [
            \Filament\Actions\ForceDeleteAction::class,
            \Filament\Tables\Actions\ForceDeleteAction::class,
            \Filament\Tables\Actions\ForceDeleteBulkAction::class,
        ];

        foreach ($forceDeletes as $forceDelete) {
            $forceDelete::configureUsing(function ($action) {
                $action->form([
                    TextInput::make('password')
                        ->password()
                        ->rule('required')
                        ->markAsRequired()
                        ->currentPassword()
                        ->helperText('Enter your password to confirm.'),
                ]);
            });
        }

        $this->app->bind(\Filament\Http\Responses\Auth\Contracts\LogoutResponse::class, \App\Http\Responses\LogoutResponse::class);
    }
}
