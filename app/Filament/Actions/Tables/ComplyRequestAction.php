<?php

namespace App\Filament\Actions\Tables;

use App\Enums\ActionStatus;
use App\Filament\Actions\Concerns\Notifications\CanNotifyUsers;
use App\Filament\Forms\FileAttachment;
use App\Models\Request;
use Exception;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;

class ComplyRequestAction extends Action
{
    use CanNotifyUsers;

    protected static ?ActionStatus $requestAction = ActionStatus::COMPLIED;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('comply-request');

        $this->label('Comply');

        $this->icon(ActionStatus::COMPLIED->getIcon());

        $this->modalIcon(ActionStatus::COMPLIED->getIcon());

        $this->successNotificationTitle('Action success');

        $this->failureNotificationTitle('Action failed');

        $this->form([
            MarkdownEditor::make('remarks')
                ->required(),
            FileAttachment::make(),
        ]);

        $this->action(function (Request $request, array $data) {
            if ($request->action->status !== ActionStatus::SUSPENDED) {
                return;
            }

            try {
                $this->beginDatabaseTransaction();

                $action = $request->actions()->create([
                    'status' => ActionStatus::COMPLIED,
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
            return $request->action->status === ActionStatus::SUSPENDED;
        });
    }
}
