<?php

namespace App\Filament\Actions\Tables;

use App\Enums\ActionStatus;
use App\Filament\Actions\Concerns\Notifications\CanNotifyUsers;
use App\Filament\Forms\FileAttachment;
use App\Models\Request;
use Exception;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;

class CompleteRequestAction extends Action
{
    use CanNotifyUsers;

    protected static ?ActionStatus $requestAction = ActionStatus::COMPLETED;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('complete-request');

        $this->label('Complete');

        $this->slideOver();

        $this->icon(ActionStatus::COMPLETED->getIcon());

        $this->modalIcon(ActionStatus::COMPLETED->getIcon());

        $this->modalHeading('Complete Request');

        $this->modalDescription('Mark this request as completed. Requester may reopen this request if needed.');

        $this->modalWidth(MaxWidth::ExtraLarge);

        $this->modalSubmitActionLabel('Confirm');

        $this->successNotificationTitle('Request completed');

        $this->failureNotificationTitle('Request completion failed');

        $this->form([
            MarkdownEditor::make('remarks')
                ->helperText('Please describe the reason for suspending this request.')
                ->required(),
            FileAttachment::make(),
        ]);

        $this->action(function (Request $request, array $data) {
            try {
                $this->beginDatabaseTransaction();

                $action = $request->actions()->create([
                    'status' => ActionStatus::COMPLETED,
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

                $this->notifyUsers();

                $this->sendSuccessNotification();
            } catch (Exception) {
                $this->rollbackDatabaseTransaction();

                $this->sendFailureNotification();
            }
        });

        $this->closeModalByClickingAway(false);

        $this->visible(fn (Request $request) => in_array($request->action->status, [ActionStatus::STARTED, ActionStatus::REOPENED, ActionStatus::RESPONDED]));
    }
}
