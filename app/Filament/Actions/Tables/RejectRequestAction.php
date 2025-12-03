<?php

namespace App\Filament\Actions\Tables;

use App\Enums\ActionStatus;
use App\Filament\Actions\Concerns\Notifications\CanNotifyUsers;
use App\Filament\Forms\FileAttachment;
use App\Models\Request;
use App\Models\User;
use Exception;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;

class RejectRequestAction extends Action
{
    use CanNotifyUsers;

    protected static ?ActionStatus $requestAction = ActionStatus::REJECTED;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('reject-request');

        $this->label('Reject');

        $this->slideOver();

        $this->icon(ActionStatus::REJECTED->getIcon());

        $this->modalIcon(ActionStatus::REJECTED->getIcon());

        $this->modalDescription('Reject this request assignment.');

        $this->modalWidth(MaxWidth::ExtraLarge);

        $this->successNotificationTitle('Request assignment rejected');

        $this->failureNotificationTitle('Request assignment rejection failed');

        $this->form([
            MarkdownEditor::make('remarks')
                ->label('Reason')
                ->required()
                ->helperText('Please provide a valid reason for rejecting this request assignment.'),
            FileAttachment::make(),
        ]);

        $this->action(function (Request $request, array $data) {
            if ($request->action->status !== ActionStatus::ASSIGNED) {
                return;
            }

            try {
                $this->beginDatabaseTransaction();

                $request->assignees()->detach(Auth::id());

                $action = $request->actions()->create([
                    'remarks' => $data['remarks'],
                    'status' => ActionStatus::REJECTED,
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
                $this->rollbackDatabaseTransaction();

                $this->sendFailureNotification();
            }
        });

        $this->closeModalByClickingAway(false);

        $this->visible(fn (Request $request) => $request->declination &&
            $request->assignees->first(fn (User $user) => $user->id === Auth::id()) &&
            $request->assignees->count() > 1 &&
            $request->action->status === ActionStatus::ASSIGNED,
        );
    }
}
