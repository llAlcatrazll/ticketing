<?php

namespace App\Filament\Actions\Tables;

use App\Enums\ActionStatus;
use App\Models\Request;
use App\Models\Subcategory;
use Exception;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;

class RecategorizeRequestAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name('recategorize-request');

        $this->label('Recategorize');

        $this->icon(ActionStatus::RECATEGORIZED->getIcon());

        $this->modalIcon(ActionStatus::RECATEGORIZED->getIcon());

        $this->modalDescription('Move this request to a different category and/or subcategory.');

        $this->modalWidth(MaxWidth::Large);

        $this->modalAlignment(Alignment::Left);

        $this->modalFooterActionsAlignment(Alignment::Right);

        $this->modalSubmitActionLabel('Confirm');

        $this->successNotificationTitle('Request recategorized');

        $this->fillForm(fn (Request $request) => ['category' => $request->subcategory_id]);

        $this->form(fn (Request $request) => [
            Select::make('category')
                ->hiddenLabel()
                ->options(
                    $request->organization
                        ->subcategories
                        ->load('category')
                        ->groupBy('category.name')
                        ->mapWithKeys(fn ($subs, $cat) => [
                            $cat => $subs->pluck('name', 'id')
                                ->map(fn ($sub) => $cat !== $sub ? "$cat — $sub" : $sub)
                                ->toArray(),
                        ])
                )
                ->disableOptionWhen(fn (string $value) => $value === $request->subcategory_id)
                ->required()
                ->placeholder(null),
        ]);

        $this->action(function (Request $request, array $data) {
            try {
                $this->beginDatabaseTransaction();

                $subcategory = Subcategory::find($data['category']);

                $category = $subcategory->category;

                $request->update([
                    'category_id' => $category->id,
                    'subcategory_id' => $subcategory->id,
                ]);

                $request->action()->create([
                    'status' => ActionStatus::RECATEGORIZED,
                    'user_id' => Auth::id(),
                    'remarks' => "From <i>*{$request->category->id} — **{$request->subcategory->id}</i> to <i>*{$category->id} — **{$subcategory->id}</i>",
                ]);

                $this->commitDatabaseTransaction();

                $this->success();
            } catch (Exception) {
                $this->rollBackDatabaseTransaction();

                $this->failure();
            }
        });

        $this->hidden(fn (Request $request) => $request->action?->status->finalized());
    }
}
