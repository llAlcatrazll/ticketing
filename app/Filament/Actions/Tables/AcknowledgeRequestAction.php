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

class AcknowledgeRequestAction extends Action
{
    use CanNotifyUsers;

    protected static ?ActionStatus $requestAction = ActionStatus::CLOSED;

    protected static ?ActionResolution $requestResolution = ActionResolution::ACKNOWLEDGED;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('acknowledge-request');

        $this->label('Acknowledge');

        $this->slideOver();

        $this->icon(ActionResolution::ACKNOWLEDGED->getIcon());

        $this->modalIcon(ActionResolution::ACKNOWLEDGED->getIcon());

        $this->modalHeading('Acknowledge request');

        $this->modalDescription('Close and acknowledge this request.');

        $this->modalSubmitActionLabel('Confirm');

        $this->modalWidth(MaxWidth::ExtraLarge);

        $this->successNotificationTitle('Request acknowledged');

        $this->failureNotificationTitle('Request acknowledgement failed');

        $this->form([
            MarkdownEditor::make('remarks')
                ->required(),
            FileAttachment::make(),
        ]);

        $this->action(function (Request $request, array $data) {
            try {
                $this->beginDatabaseTransaction();

                $action = $request->actions()->create([
                    'status' => ActionStatus::CLOSED,
                    'user_id' => Auth::id(),
                    'remarks' => $data['remarks'],
                    'resolution' => ActionResolution::ACKNOWLEDGED,
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

        $this->hidden(fn (Request $record) => $record->action->status === ActionStatus::CLOSED);

        $this->visible(fn (Request $record) => $record->class === RequestClass::SUGGESTION);
    }
}
