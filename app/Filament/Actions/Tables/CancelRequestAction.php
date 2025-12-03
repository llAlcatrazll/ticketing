<?php

namespace App\Filament\Actions\Tables;

use App\Enums\ActionResolution;
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

class CancelRequestAction extends Action
{
    use CanNotifyUsers;

    protected static ?ActionStatus $requestAction = ActionStatus::CLOSED;

    protected static ?ActionResolution $requestResolution = ActionResolution::CANCELLED;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('cancel-request');

        $this->label('Cancel');

        $this->slideOver();

        $this->icon(ActionResolution::CANCELLED->getIcon());

        $this->modalIcon(ActionResolution::CANCELLED->getIcon());

        $this->modalHeading('Cancel request');

        $this->modalDescription('Cancel this request to prevent further processing.');

        $this->modalWidth(MaxWidth::ExtraLarge);

        $this->closeModalByClickingAway(false);

        $this->successNotificationTitle('Request cancelled');

        $this->failureNotificationTitle('Request closure failed');

        $this->form([
            MarkdownEditor::make('remarks')
                ->required()
                ->helperText('Please provide a reason for cancelling this request.'),
            FileAttachment::make(),
        ]);

        $this->action(function (Request $request, array $data) {
            try {
                $this->beginDatabaseTransaction();

                $action = $request->actions()->create([
                    'status' => ActionStatus::CLOSED,
                    'resolution' => ActionResolution::CANCELLED,
                    'remarks' => $data['remarks'],
                    'user_id' => Auth::id(),
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
                $this->rollBackDatabaseTransaction();

                $this->sendFailureNotification();
            }
        });

        $this->disabled(function (Request $request) {
            return match ($request->class) {
                RequestClass::TICKET => $request->action->created_at->addHours(24)->greaterThan(now()) &&
                    in_array($request->action->status, [
                        ActionStatus::COMPLETED,
                        ActionStatus::CLOSED,
                    ]),
                RequestClass::INQUIRY => $request->action->created_at->addHours(24)->greaterThan(now()) &&
                    in_array($request->action->status, [
                        ActionStatus::CLOSED,
                    ]),
                RequestClass::SUGGESTION => $request->action->created_at->addHours(24)->greaterThan(now()) &&
                    in_array($request->action->status, [
                        ActionStatus::CLOSED,
                    ]),
            };
        });
    }
}
