<?php

namespace App\Filament\Actions\Concerns;

use App\Enums\ActionStatus;
use App\Enums\RequestClass;
use App\Models\Request;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\MaxWidth;

trait ShowRequest
{
    protected function bootShowRequest(): void
    {
        $this->icon('heroicon-o-eye');

        $this->label('Show');

        $this->color('gray');

        $this->slideOver();

        $this->modalIcon(fn (Request $request) => $request->class->getIcon());

        $this->modalIconColor(fn (Request $request) => $request->class->getColor());

        $this->modalHeading(fn (Request $request) => str("Show {$request->class->value} <span class='font-mono'>#{$request->code}</span>")->toHtmlString());

        $this->modalDescription(fn (Request $request) => $request->user->name);

        $this->modalFooterActionsAlignment(Alignment::End);

        $this->modalSubmitAction(false);

        $this->modalCancelAction(false);

        $this->modalWidth(MaxWidth::ExtraLarge);

        $this->infolist(fn (Request $request) => [
            TextEntry::make('tags')
                ->hiddenLabel()
                ->badge()
                ->alignEnd()
                ->hidden(fn (Request $request) => $request->tags->isEmpty())
                ->color(fn (string $state) => $request->tags->first(fn ($tag) => $tag->name === $state)?->color ?? 'gray')
                ->state(fn (Request $request) => $request->tags->pluck('name')->toArray()),
            TextEntry::make('from.name')
                ->hiddenLabel()
                ->helperText(fn (Request $request) => $request->user->name),
            TextEntry::make('action.status')
                ->hiddenLabel()
                ->size(TextEntry\TextEntrySize::ExtraSmall)
                ->state(fn (Request $request) => $request->action->status === ActionStatus::CLOSED ? $request->action->resolution : $request->action->status),
            TextEntry::make('subject')
                ->hiddenLabel()
                ->weight(FontWeight::Bold)
                ->size(TextEntry\TextEntrySize::Large),
            TextEntry::make('submitted.created_at')
                ->hiddenLabel()
                ->color('gray')
                ->dateTime('F j, Y \a\t H:i'),
            ViewEntry::make('body')
                ->label('Inquiry')
                ->hiddenLabel(false)
                ->view('filament.requests.show', [
                    'request' => $request,
                ]),
            ViewEntry::make('responses')
                ->visible($request->class === RequestClass::INQUIRY)
                ->view('filament.requests.history', [
                    'request' => $request,
                    'chat' => true,
                    'descending' => false,
                ]),
        ]);

        $this->hidden(fn (Request $request) => $request->trashed());
    }
}
