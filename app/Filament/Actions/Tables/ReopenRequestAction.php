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

class ReopenRequestAction extends Action
{
    use CanNotifyUsers;

    protected static ?ActionStatus $requestAction = ActionStatus::REOPENED;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('reopen-request');

        $this->label('Reopen');

        $this->slideOver();

        $this->icon(ActionStatus::REOPENED->getIcon());

        $this->modalIcon(ActionStatus::REOPENED->getIcon());

        $this->modalDescription('Reopen this request if you think it has not been resolved yet.');

        $this->modalWidth(MaxWidth::ExtraLarge);

        $this->successNotificationTitle('Request reopened');

        $this->failureNotificationTitle('Request reopening failed');

        $this->form([
            MarkdownEditor::make('remarks')
                ->required()
                ->helperText('Please provide a brief reason for reopening this request.'),
            FileAttachment::make(),
        ]);

        $this->action(function (Request $request, array $data) {
            if ($request->action->status !== ActionStatus::COMPLETED) {
                return;
            }

            try {
                $this->beginDatabaseTransaction();

                $action = $request->actions()->create([
                    'remarks' => $data['remarks'],
                    'status' => ActionStatus::REOPENED,
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

        $this->visible(fn (Request $request) => $request->action->status === ActionStatus::COMPLETED);
    }
}
