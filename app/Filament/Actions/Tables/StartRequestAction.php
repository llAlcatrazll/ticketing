<?php

namespace App\Filament\Actions\Tables;

use App\Enums\ActionStatus;
use App\Enums\RequestClass;
use App\Filament\Actions\Concerns\Notifications\CanNotifyUsers;
use App\Models\Request;
use Exception;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;

class StartRequestAction extends Action
{
    use CanNotifyUsers;

    protected static ?ActionStatus $requestAction = ActionStatus::STARTED;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('start-request');

        $this->label('Start');

        $this->icon(ActionStatus::STARTED->getIcon());

        $this->requiresConfirmation();

        $this->modalHeading('Start processing request');

        $this->modalDescription('Start this request to begin processing. Once started, the request will be marked as in progress.');

        $this->successNotificationTitle(fn (Request $request) => "{$request->class->getLabel()} request #{$request->code} started");

        $this->failureNotificationTitle(fn (Request $request) => "Failed to start {$request->class->getLabel()} request #{$request->code}");

        $this->action(function (Request $request) {
            if ($request->class !== RequestClass::TICKET || in_array($request->action->status, [ActionStatus::STARTED, ActionStatus::SUSPENDED])) {
                return;
            }

            try {
                $this->beginDatabaseTransaction();

                $request->actions()->create([
                    'status' => ActionStatus::STARTED,
                    'user_id' => Auth::id(),
                ]);

                $this->commitDatabaseTransaction();

                $this->sendSuccessNotification();

                $this->notifyUsers();
            } catch (Exception) {
                $this->rollbackDatabaseTransaction();

                $this->sendFailureNotification();
            }
        });

        $this->visible(function (Request $request) {
            if ($request->action->status->finalized() || in_array($request->action->status, [
                ActionStatus::STARTED,
                ActionStatus::SUSPENDED,
                ActionStatus::COMPLETED,
            ])) {
                return false;
            }

            return in_array($request->action->status, [ActionStatus::ASSIGNED, ActionStatus::COMPLIED, ActionStatus::REOPENED]) && match ($request->class) {
                RequestClass::TICKET => $request->assignees->contains(Auth::user()),
                default => false,
            };
        });
    }
}
