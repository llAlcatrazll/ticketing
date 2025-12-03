<?php

namespace App\Filament\Actions\Tables;

use App\Enums\ActionResolution;
use App\Enums\ActionStatus;
use App\Filament\Actions\Concerns\Notifications\CanNotifyUsers;
use App\Filament\Forms\FileAttachment;
use App\Models\Request;
use Exception;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;


class ResolveRequestAction extends Action
{
    use CanNotifyUsers;

    protected static ?ActionStatus $requestAction = ActionStatus::CLOSED;

    protected static ?ActionResolution $requestResolution = ActionResolution::RESOLVED;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('resolve-request');

        $this->label('Close');

        $this->slideOver();

        $this->icon(ActionResolution::RESOLVED->getIcon());

        $this->modalIcon(ActionResolution::RESOLVED->getIcon());

        $this->modalHeading('Close request');

        $this->modalDescription('Permanently close this request and mark it as resolved.');

        $this->modalWidth(MaxWidth::ExtraLarge);

        $this->closeModalByClickingAway(false);

        $this->successNotificationTitle('Request closed');

        $this->failureNotificationTitle('Request closure failed');

        $this->form([
            MarkdownEditor::make('remarks'),
            FileAttachment::make(),
        ]);

        $this->action(function (Request $request, array $data) {
            if ($request->action->status !== ActionStatus::COMPLETED) {
                return;
            }

            try {
                $this->beginDatabaseTransaction();

                $action = $request->actions()->create([
                    'status' => ActionStatus::CLOSED,
                    'resolution' => ActionResolution::RESOLVED,
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

                Notification::make()
                    ->persistent()
                    ->title('Request closed')
                    ->body('Would you like to provide feedback?')
                    ->actions([
                        NotificationAction::make('feedback')
                            ->label('Give Feedback')
                            ->url(route('filament.feedback.feedback', [
                                'organization' => $request->organization_id,
                            ])),
                    ])
                    ->success()
                    ->send();

                $this->notifyUsers();
            } catch (Exception) {
                $this->rollBackDatabaseTransaction();

                $this->sendFailureNotification();
            }
        });

        $this->hidden(fn (Request $request) => $request->action->status->finalized() ?: $request->action->status !== ActionStatus::COMPLETED);
    }
}
