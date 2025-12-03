<?php

namespace App\Filament\Actions\Tables;

use App\Enums\ActionStatus;
use App\Enums\RequestClass;
use App\Enums\UserRole;
use App\Filament\Actions\Concerns\Notifications\CanNotifyUsers;
use App\Models\Request;
use App\Models\User;
use Exception;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;

class AssignRequestAction extends Action
{
    use CanNotifyUsers;

    protected static ?ActionStatus $requestAction = ActionStatus::ASSIGNED;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('assign-request');

        $this->label(fn (Request $request) => $request->action->status === ActionStatus::ASSIGNED ? 'Reassign' : 'Assign');

        $this->icon(ActionStatus::ASSIGNED->getIcon());

        $this->slideOver();

        $this->modalIcon(ActionStatus::ASSIGNED->getIcon());

        $this->modalHeading(fn (Request $request) => ($request->action->status === ActionStatus::ASSIGNED ? 'Reassign' : 'Assign').' request');

        $this->modalDescription('Please select support users to assign this request to.');

        $this->modalContent(fn (Request $request) => $request->organization->users()->agent(moderators: true, admin: Auth::user()->role !== UserRole::MODERATOR)->doesntExist() ? str('No support users found')->toHtmlString() : null);

        $this->modalWidth(MaxWidth::ExtraLarge);

        $this->modalSubmitAction(fn (Request $request) => $request->organization->users()->agent(moderators: true, admin: Auth::user()->role !== UserRole::MODERATOR)->exists() ? null : false);

        $this->modalSubmitActionLabel('Assign');

        $this->successNotificationTitle(fn (Request $request) => $request->action->status === ActionStatus::ASSIGNED ? 'Request reassigned' : 'Request assigned');

        $this->failureNotificationTitle('Failed to assign request');

        $this->fillForm(fn (Request $request) => [
            'declination' => $request->declination,
            'assignees' => $request->assignees->pluck('id')->toArray(),
        ]);

        $this->form(fn (Request $request) => $request->organization->users()->agent(moderators: true, admin: Auth::user()->role !== UserRole::MODERATOR)->approvedAccount()->exists() ? [
            Toggle::make('declination')
                ->label('Allow declination')
                ->helperText('Allow assignees to have the option to decline the assignment')
                ->default(true),
            // MarkdownEditor::make('remarks'),
            CheckboxList::make('assignees')
                ->required()
                ->searchable()
                ->exists('users', 'id')
                ->options(
                    $request->organization->users()
                        ->agent(moderators: true, admin: Auth::user()->role !== UserRole::MODERATOR)
                        ->approvedAccount()
                        ->sortByRole(false)
                        ->get(['id', 'name', 'role'])
                        ->mapWithKeys(fn ($user) => [$user->id => "{$user->name} ({$user->role->getLabel()}) ".(Auth::id() === $user->id ? '(you)' : '')])
                        ->toArray()
                )
                ->descriptions($request->organization->users()->agent(moderators: true, admin: Auth::user()->role !== UserRole::MODERATOR)->approvedAccount()->pluck('designation', 'id')->toArray()),
        ] : []);

        $this->action(function (Request $request, array $data) {
            if (
                $request->assignees->pluck('id')->diff($data['assignees'])->isEmpty() &&
                collect($data['assignees'])->diff($request->assignees->pluck('id')->toArray())->isEmpty()
            ) {
                Notification::make()
                    ->info()
                    ->title('No changes made')
                    ->body('As there are no changes to assignees, action was not performed.')
                    ->send();

                return;
            }

            try {
                $this->beginDatabaseTransaction();

                $request->update([
                    'declination' => $data['declination'],
                ]);

                $request->actions()->create([
                    'status' => ActionStatus::ASSIGNED,
                    'user_id' => Auth::id(),
                    'remarks' => User::select('id')
                        ->find($data['assignees'])
                        ->map(fn (User $user) => ['id' => "* {$user->id}"])
                        ->implode('id', "\n"),
                ]);

                $request->assignees()->sync(
                    collect($data['assignees'])->mapWithKeys(function (string $assigned) use ($request) {
                        $assignee = $request->assignees->first(fn (User $assignee) => $assignee->id === $assigned);

                        return [$assigned => [
                            'assigner_id' => Auth::id(),
                            'response' => $assignee?->pivot->response,
                            'responded_at' => $assignee?->pivot->responded_at,
                            'created_at' => $assignee?->pivot->created_at ?? now(),
                        ]];
                    }),
                );

                $this->commitDatabaseTransaction();

                $this->sendSuccessNotification();

                $this->notifyUsers();
            } catch (Exception) {
                $this->rollBackDatabaseTransaction();

                $this->sendFailureNotification();
            }
        });

        $this->visible(function (Request $request) {
            return match ($request->class) {
                RequestClass::TICKET => in_array($request->action->status, [ActionStatus::QUEUED, ActionStatus::ASSIGNED]),
                RequestClass::INQUIRY => in_array($request->action->status, [ActionStatus::RESPONDED, ActionStatus::SUBMITTED, ActionStatus::ASSIGNED]),
                RequestClass::SUGGESTION => in_array($request->action->status, [ActionStatus::SUBMITTED, ActionStatus::ASSIGNED]),
                default => false,
            };
        });
    }
}
