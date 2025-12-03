<?php

namespace App\Filament\Actions\Tables;

use App\Enums\ActionStatus;
use App\Enums\RequestClass;
use App\Filament\Actions\Concerns\Notifications\CanNotifyUsers;
use App\Filament\Forms\FileAttachment;
use App\Models\Request;
use Exception;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;

class SuspendRequestAction extends Action
{
    use CanNotifyUsers;

    protected static ?ActionStatus $requestAction = ActionStatus::SUSPENDED;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('suspend-request');

        $this->label('Suspend');

        $this->icon(ActionStatus::SUSPENDED->getIcon());

        $this->modalIcon(ActionStatus::SUSPENDED->getIcon());

        $this->slideOver();

        $this->modalHeading('Suspend Request');

        $this->modalDescription('Suspend this on-going request. This will hold the request until requester complies with the requirements.');

        $this->modalWidth(MaxWidth::ExtraLarge);

        $this->closeModalByClickingAway(false);

        $this->modalSubmitActionLabel('Confirm');

        $this->successNotificationTitle('Request suspended');

        $this->failureNotificationTitle('Failed to suspend request');

        $this->form([
            MarkdownEditor::make('remarks')
                ->label('Reason')
                ->required()
                ->helperText('Please describe the reason for suspending this request.'),
            FileAttachment::make(),
        ]);

        $this->action(function (Request $request, array $data) {
            if ($request->class !== RequestClass::TICKET || $request->action->status === ActionStatus::SUSPENDED) {
                return;
            }

            try {
                $this->beginDatabaseTransaction();

                $action = $request->actions()->create([
                    'status' => ActionStatus::SUSPENDED,
                    'user_id' => Auth::id(),
                    'remarks' => $data['remarks'],
                ]);

                if (count($data['files']) > 0) {
                    $action->attachment()->create([
                        'files' => $data['files'],
                        'paths' => $data['paths'],
                    ]);
                }

                $this->commitDatabaseTransaction();

                $this->sendSuccessNotification();

                $this->notifyUsers();
            } catch (Exception) {
                $this->rollbackDatabaseTransaction();

                $this->sendFailureNotification();
            }
        });

        $this->visible(function (Request $request) {
            if ($request->action->status->finalized() || $request->action->status === ActionStatus::SUSPENDED) {
                return false;
            }

            return in_array($request->action->status, [ActionStatus::STARTED, ActionStatus::COMPLIED]) && match ($request->class) {
                RequestClass::TICKET => $request->assignees->contains(Auth::user()),
                default => false,
            };
        });
    }
}
