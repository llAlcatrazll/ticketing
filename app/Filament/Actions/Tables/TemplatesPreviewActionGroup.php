<?php

namespace App\Filament\Actions\Tables;

use App\Enums\RequestClass;
use App\Models\Subcategory;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;

class TemplatesPreviewActionGroup extends ActionGroup
{
    public static function make(array $actions = []): static
    {
        $static = app(static::class, ['actions' => $actions]);

        $static->configure();

        return $static;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Templates');

        $this->link();

        $this->icon('gmdi-notes');

        $this->actions(array_map(fn (RequestClass $class) => Action::make($class->value)
            ->icon($class->getIcon())
            ->label($class->getLabel())
            ->slideOver()
            ->modalFooterActionsAlignment(Alignment::End)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->modalWidth(MaxWidth::ExtraLarge)->infolist(fn (Subcategory $subcategory) => [
                TextEntry::make('preview')
                    ->hiddenLabel()
                    ->extraEntryWrapperAttributes(['class' => 'w-full'])
                    ->state(str($subcategory->{"{$class->value}Template"}?->content ?? '')->markdown()->toHtmlString())
                    ->markdown(),
            ]),
            RequestClass::cases()
        ));
    }
}
