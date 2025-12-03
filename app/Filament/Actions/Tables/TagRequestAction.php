<?php

namespace App\Filament\Actions\Tables;

use App\Enums\ActionStatus;
use App\Models\Request;
use App\Models\Tag;
use Exception;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;

class TagRequestAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name('tag-request');

        $this->label('Tag');

        $this->icon(ActionStatus::TAGGED->getIcon());

        $this->modalDescription('Label this request with topics that apply.');

        $this->modalIcon(ActionStatus::TAGGED->getIcon());

        $this->modalWidth(MaxWidth::Small);

        $this->modalAlignment(Alignment::Left);

        $this->modalFooterActionsAlignment(Alignment::Right);

        $this->fillForm(fn (Request $request) => [
            'labels' => $request->tags->pluck('id'),
        ]);

        $this->form(fn (Request $request) => [
            Select::make('labels')
                ->options($request->organization->tags->pluck('name', 'id'))
                ->multiple(),
        ]);

        $this->action(function (Request $request, array $data) {
            if ($request->tags->pluck('name')->toArray() === $data['labels']) {
                return;
            }

            try {
                $this->beginDatabaseTransaction();

                $added = array_diff($data['labels'], $request->tags->pluck('id')->toArray());

                $removed = array_diff($request->tags->pluck('id')->toArray(), $data['labels']);

                $remarks = $removed ? '-'.Tag::find($removed)->pluck('id')->implode('-') : null;

                $remarks .= $added ? '+'.Tag::find($added)->pluck('id')->implode('+') : null;

                $request->actions()->create([
                    'status' => ActionStatus::TAGGED,
                    'remarks' => $remarks,
                    'user_id' => Auth::id(),
                ]);

                $request->tags()->sync($data['labels']);

                $this->commitDatabaseTransaction();
            } catch (Exception) {
                $this->rollbackDatabaseTransaction();
            }

            $request->tags()->sync($data['labels']);
        });

        $this->disabled(fn (Request $request) => $request->action->status->finalized() &&
            $request->action->created_at->addDays(90)->lessThan(now())
        );
    }
}
