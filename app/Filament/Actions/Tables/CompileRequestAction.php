<?php

namespace App\Filament\Actions\Tables;

use App\Filament\Clusters\Dossiers;
use App\Filament\Clusters\Dossiers\Resources\AllDossierResource;
use App\Models\Dossier;
use App\Models\Request;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;

class CompileRequestAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name('compile-request');

        $this->label('Compile');

        $this->modalDescription('Compile this request into a dossier.');

        $this->icon(Dossiers::getNavigationIcon());

        $this->modalIcon(Dossiers::getNavigationIcon());

        $this->modalWidth(MaxWidth::ExtraLarge);

        $this->modalSubmitActionLabel('Compile');

        $this->slideOver();

        $this->form([
            Select::make('dossier')
                ->required()
                ->relationship(
                    'dossiers', 'name',
                    fn ($query, $record) => $query->whereDoesntHave('requests', function ($query) use ($record) {
                        $query->where('requests.id', $record->id);
                    })
                )
                ->preload()
                ->searchable()
                ->noSearchResultsMessage('No dossiers found.')
                ->placeholder('Select a dossier')
                ->createOptionForm(fn (HasForms $livewire) => [
                    ...AllDossierResource::form(new Form($livewire))->getComponents(),
                    Hidden::make('organization_id')
                        ->default(Auth::user()->organization_id),
                ])
                ->createOptionAction(function ($action) {
                    $action->label('New dossier')
                        ->modalHeading('Create new dossier')
                        ->slideOver()
                        ->modalWidth(MaxWidth::ExtraLarge);
                })
                ->rule(fn (Request $request) => function ($a, $v, $f) use ($request) {
                    if ($request->dossiers()->where('dossier_id', $v)->exists()) {
                        return 'This dossier already exists in this request.';
                    }
                }),
        ]);

        $this->action(function (array $data) {
            Notification::make()
                ->title('Request compiled to '.Dossier::find($data['dossier'])->name)
                ->success()
                ->send();
        });
    }
}
