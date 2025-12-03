<?php

namespace App\Filament\Actions\Tables;

use Filament\Tables\Actions\Action;

class TakeFeedbackAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name('take-feedback');

        $this->label('Take Feedback');

        $this->openUrlInNewTab();

        $this->icon('heroicon-o-chat-bubble-left-right');

        $this->hidden(fn ($record) => $record->feedback()->exists());

        $this->url(fn ($record) => route('filament.feedback.feedback', ['organization' => $record->organization_id, 'request' => $record->id]));
    }
}
