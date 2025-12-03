<?php

namespace App\Filament\Actions\Tables;

use App\Enums\ActionStatus;
use App\Enums\RequestClass;
use App\Models\Request;
use Exception;
use Filament\Forms\Components\Radio;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;

class ReclassifyRequestAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name('reclassify-request');

        $this->label('Reclassify');

        $this->icon(ActionStatus::RECLASSIFIED->getIcon());

        $this->slideOver();

        $this->modalIcon(ActionStatus::RECLASSIFIED->getIcon());

        $this->modalDescription('Reclassify this request to best fit its nature.');

        $this->modalWidth(MaxWidth::ExtraLarge);

        $this->successNotificationTitle('Request reclassified');

        $this->form(fn (Request $request) => [
            Radio::make('class')
                ->options(RequestClass::class)
                ->default($request->class)
                ->disableOptionWhen(fn (string $value) => $value === $request->class->value)
                ->rule('required')
                ->markAsRequired()
                ->rule(fn () => function ($attribute, $value, $fail) use ($request) {
                    if ($value === $request->class) {
                        $fail('The request is already classified as '.$request->class->getLabel().'.');
                    }
                }),
        ]);

        $this->action(function (Request $request, array $data) {
            try {
                $this->beginDatabaseTransaction();

                $old = $request->class;

                $new = $data['class'];

                $request->update([
                    'class' => $new,
                ]);

                $request->actions()->create([
                    'status' => ActionStatus::RECLASSIFIED,
                    'user_id' => Auth::id(),
                    'remarks' => 'From *'.$old->getLabel().'* to *'.$new->getLabel().'*',
                ]);

                $this->commitDatabaseTransaction();

                $this->sendSuccessNotification();
            } catch (Exception $e) {
                $this->rollbackDatabaseTransaction();

                throw $e;
            }
        });

        $this->hidden(function (Request $request) {
            return $request->action?->status->finalized() ?:
                $request->actions->some(fn (\App\Models\Action $action) => in_array($action->status, [
                    ActionStatus::STARTED,
                    ActionStatus::RESPONDED,
                ], true));
        });
    }
}
