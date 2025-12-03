<?php

namespace App\Filament\Actions\Tables;

use App\Enums\ActionStatus;
use App\Models\Request;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;

class UndoRecentAction extends Action
{
    private static int $duration = 5;

    private static array $undoable = [
        ActionStatus::QUEUED,
        ActionStatus::STARTED,
        ActionStatus::SUSPENDED,
        ActionStatus::COMPLETED,
        ActionStatus::CLOSED,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('undo-recent-action-request');

        $this->label('Undo');

        $this->icon('gmdi-change-circle-o');

        $this->modalIcon('gmdi-change-circle-o');

        $this->requiresConfirmation();

        $this->modalHeading(fn (Request $request) => "Undo {$request->action->status->getLabel('nounForm', false)}");

        $this->modalDescription(fn (Request $request) => 'For '.static::$duration." minutes, you are allowed to undo your recent action. Are you sure you want to undo the {$request->action->status->getLabel('nounForm', false)}?");

        $this->successNotificationTitle(fn (Request $request) => "Request {$request->action->status->getLabel('nounForm', false)} undone");

        $this->action(function (Request $request) {
            if (in_array($request->action->status, static::$undoable) === false) {
                return;
            }

            $request->action->delete();

            $this->sendSuccessNotification();
        });

        $this->visible(fn (Request $request) => in_array($request->action->status, static::$undoable) &&
            $request->action->created_at->addMinutes(static::$duration)->greaterThan(now()) &&
            $request->action->user_id === Auth::id(),
        );
    }
}
