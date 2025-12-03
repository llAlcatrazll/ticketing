<?php

namespace App\Filament\Actions\Tables;

use App\Enums\ActionStatus;
use App\Enums\UserRole;
use App\Filament\Actions\Concerns\Notifications\CanNotifyUsers;
use App\Filament\Forms\FileAttachment;
use App\Models\Request;
use Exception;
use Filament\Facades\Filament;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;

class RequeueRequestAction extends Action
{
    use CanNotifyUsers;

    protected static ?ActionStatus $requestAction = ActionStatus::REJECTED;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('requeue-request');

        $this->label('Requeue');

        $this->slideOver();

        $this->icon(ActionStatus::QUEUED->getIcon());

        $this->modalIcon(ActionStatus::QUEUED->getIcon());

        $this->modalDescription('Please provide a valid reason for rejecting and requeueing this request.');

        $this->modalWidth(MaxWidth::ExtraLarge);

        $this->successNotificationTitle('Request rejected and requeued');

        $this->failureNotificationTitle('Request rejection and requeue failed');

        $this->form([
            MarkdownEditor::make('remarks')
                ->label('Reason')
                ->required(Filament::getCurrentPanel()->getId() === 'agent'),
            FileAttachment::make(),
        ]);

        $this->action(function (Request $request, array $data) {
            if ($request->action->status !== ActionStatus::ASSIGNED) {
                return;
            }

            try {
                $this->beginDatabaseTransaction();

                $request->assignees()->detach();

                $action = $request->actions()->create([
                    'remarks' => $data['remarks'],
                    'status' => ActionStatus::QUEUED,
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

        $this->closeModalByClickingAway(false);

        $this->hidden(fn (Request $request) => $request->action->status->finalized() ?: $request->action->status === ActionStatus::QUEUED);

        $this->visible(fn (Request $request) => $request->action->status === ActionStatus::ASSIGNED &&
            in_array(Auth::user()->role, [UserRole::ADMIN, UserRole::MODERATOR]) ?:
            $request->declination === true &&
            $request->assignees()->count() === 1 &&
            $request->action->status === ActionStatus::ASSIGNED,
        );
    }
}
