<?php

namespace App\Filament\Actions;

use App\Enums\RequestClass;
use App\Models\Organization;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\MaxWidth;
use Livewire\Component;

class NewRequestPromptAction extends Action
{
    protected RequestClass $class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('new-request-prompt');

        $this->label(fn () => "New {$this->class->value}");

        $this->modalIcon('heroicon-o-plus');

        $this->modalWidth(MaxWidth::Large);

        $this->modalDescription('Which organization do you want to create a new request for?');

        $this->modalSubmitActionLabel('Proceed');

        $this->form(function () {
            $organizations = Organization::query()
                ->whereHas('subcategories')
                ->get(['organizations.name', 'organizations.code', 'organizations.id'])
                ->mapWithKeys(fn ($organization) => [$organization->id => "{$organization->code} — {$organization->name}"])
                ->toArray();

            return [
                Select::make('organization')
                    ->hiddenLabel()
                    ->options($organizations)
                    ->default(count($organizations) === 1 ? key($organizations) : null)
                    ->placeholder('Select an organization')
                    ->required()
                    ->validationMessages([
                        'required' => 'Please select an organization.',
                    ]),
            ];
        });

        $this->action(function (Component $livewire, array $data) {
            $this->redirect($livewire->getResource()::getUrl('new', ['record' => $data['organization']]));
        });
    }

    public function class(RequestClass $class): static
    {
        $this->class = $class;

        return $this;
    }
}
