<?php

namespace App\Filament\Actions\Tables;

use App\Models\Request;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;

class ViewRequestHistoryAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name('view-request-history');

        $this->label('History');

        $this->icon('gmdi-history-o');

        $this->slideOver();

        $this->modalIcon('gmdi-history-o');

        $this->modalWidth(MaxWidth::ExtraLarge);

        $this->modalHeading('Request History');

        $this->modalDescription('See the history of this request.');

        $this->modalSubmitAction(false);

        $this->modalCancelAction(false);

        $this->modalContent(fn (Request $request) => view('filament.requests.history', ['request' => $request]));

        $this->hidden(fn (Request $request) => $request->trashed());
    }
}
