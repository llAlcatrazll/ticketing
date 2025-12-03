<?php

namespace App\Filament\Actions\Concerns;

use App\Enums\ActionStatus;
use App\Filament\Actions\Concerns\Notifications\CanNotifyUsers;
use App\Models\Request;
use Exception;
use Illuminate\Support\Facades\Auth;

trait ResubmitRequest
{
    use CanNotifyUsers;

    protected static ?ActionStatus $requestAction = ActionStatus::SUBMITTED;

    protected function bootResubmitRequest()
    {
        $this->name('resubmit-request');

        $this->label('Resubmit');

        $this->icon('gmdi-publish-o');

        $this->requiresConfirmation();

        $this->modalHeading('Resubmit request');

        $this->modalIcon('gmdi-publish-o');

        $this->successNotificationTitle('Request resubmitted');

        $this->failureNotificationTitle('Failed to resubmit request');

        $this->action(function (Request $request): void {
            try {
                $this->beginDatabaseTransaction();

                $request->actions()->create(['user_id' => Auth::id(), 'status' => ActionStatus::SUBMITTED]);

                $this->commitDatabaseTransaction();

                $this->sendSuccessNotification();

                $this->notifyUsers();
            } catch (Exception) {
                $this->rollbackDatabaseTransaction();

                $this->sendFailureNotification();
            }
        });

        $this->visible(fn (Request $request): bool => is_null($request->action) ?: in_array($request->action?->status, [
            ActionStatus::RECALLED,
            ActionStatus::RESTORED,
        ]));

        $this->hidden(fn (Request $request): bool => $request->trashed());
    }
}
