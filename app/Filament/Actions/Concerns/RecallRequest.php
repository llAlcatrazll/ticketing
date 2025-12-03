<?php

namespace App\Filament\Actions\Concerns;

use App\Enums\ActionStatus;
use App\Models\Request;
use Illuminate\Support\Facades\Auth;

trait RecallRequest
{
    private static int $duration = 15;

    private static int $cancellation = 24;

    protected function bootRecallRequest()
    {
        $this->name('recall-request');

        $this->label('Recall');

        $this->icon(ActionStatus::RECALLED->getIcon());

        $this->requiresConfirmation();

        $this->modalHeading('Recall request');

        $this->modalDescription(
            'For '.static::$duration.' minutes since the last submission, you may recall this request.
            This will allow you to make changes before resubmitting the request.
            Subsequently, recalled requests after '.static::$cancellation.' hours will be automatically cancelled.
            Are you sure you want to recall this request?'
        );

        $this->modalIcon(ActionStatus::RECALLED->getIcon());

        $this->successNotificationTitle('Request recalled');

        $this->action(function (Request $request) {
            $request->actions()->create(['user_id' => Auth::id(), 'status' => ActionStatus::RECALLED]);

            $this->sendSuccessNotification();
        });

        $this->disabled(fn (Request $request) => $request->action?->status !== ActionStatus::SUBMITTED || $request->action->created_at->addMinutes(static::$duration)->lessThan(now()));

        $this->hidden(fn (Request $request) => $request->action?->status === ActionStatus::RECALLED);
    }
}
