<?php

namespace App\Filament\Actions\Tables;

use App\Enums\ActionResolution;
use App\Enums\ActionStatus;
use App\Filament\Actions\Concerns\Notifications\CanNotifyUsers;
use App\Models\Request;
use Exception;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Radio;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;

class CloseRequestAction extends Action
{
    use CanNotifyUsers;

    protected static ?ActionStatus $requestAction = ActionStatus::CLOSED;

    protected static ?ActionResolution $requestResolution = null;

    protected bool $remarksRequired = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('close-request');

        $this->label('Close');

        $this->slideOver();

        $this->icon(ActionStatus::CLOSED->getIcon());

        $this->modalIcon(ActionStatus::CLOSED->getIcon());

        $this->modalHeading('Close request');

        $this->modalDescription('Close this request prematurely.');

        $this->modalWidth(MaxWidth::ExtraLarge);

        $this->closeModalByClickingAway(false);

        $this->successNotificationTitle('Request closed');

        $this->failureNotificationTitle('Request closure failed');

        $this->form([
            Radio::make('resolution')
                ->options([
                    ActionResolution::UNRESOLVED->value => ActionResolution::UNRESOLVED->getLabel(),
                    ActionResolution::INVALIDATED->value => ActionResolution::INVALIDATED->getLabel(),
                ])
                ->descriptions([
                    ActionResolution::UNRESOLVED->value => ActionResolution::UNRESOLVED->getDescription(),
                    ActionResolution::INVALIDATED->value => ActionResolution::INVALIDATED->getDescription(),
                ])
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, $old, $set) {
                    $set('remarks', ActionResolution::from($state)->remarks());
                }),
            MarkdownEditor::make('remarks')
                ->helperText('Please provide a brief reason for closing this request.')
                ->required(fn () => $this->remarksRequired),
        ]);

        $this->action(function (Request $request, array $data) {
            try {
                $this->beginDatabaseTransaction();

                $request->actions()->create([
                    'status' => ActionStatus::CLOSED,
                    'resolution' => $data['resolution'],
                    'remarks' => $data['remarks'],
                    'user_id' => Auth::id(),
                ]);

                $this->commitDatabaseTransaction();

                $this->sendSuccessNotification();

                $this->notifyUsers();
            } catch (Exception $ex) {
                $this->rollBackDatabaseTransaction();

                $this->sendFailureNotification();

                throw $ex;
            }
        });

        $this->hidden(fn (Request $request) => $request->action->status->finalized() ?: $request->action->status === ActionStatus::COMPLETED);
    }

    public function requireRemarks(bool $required = true)
    {
        $this->remarksRequired = $required;

        return $this;
    }
}
