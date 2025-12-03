<?php

namespace App\Filament\Concerns;

use App\Enums\ActionStatus;
use App\Enums\RequestClass;
use App\Filament\Actions\Concerns\Notifications\CanNotifyUsers;
use App\Filament\Forms\FileAttachment;
use App\Models\Organization;
use App\Models\Request;
use App\Models\Subcategory;
use Filament\Actions\Action;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Support\Facades\FilamentView;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

use function Filament\Support\is_app_url;

trait NewRequest
{
    use CanNotifyUsers, EvaluatesClosures;

    protected static ?ActionStatus $requestAction = ActionStatus::SUBMITTED;

    public function mount(int|string $record): void
    {
        $this->record = Organization::findOrFail($record);

        $this->form->fill();
    }

    public function getHeading(): string|Htmlable
    {
        $classification = static::getClassification();

        $heading = <<<HTML
            <span class="text-custom-600 dark:text-custom-400" style="--c-400:var(--danger-400);--c-600:var(--danger-600);">
                New $classification->value
            </span>
            request for
            <span class="text-custom-600 dark:text-custom-400" style="--c-400:var(--primary-400);--c-600:var(--primary-600);">
                {$this->record->code}
            </span>
        HTML;

        return str($heading)->toHtmlString();
    }

    public function form(Form $form): Form
    {
        $classification = static::getClassification();

        $subcategories = $this->record->subcategories
            ->load('category')
            ->groupBy('category.name')
            ->mapWithKeys(fn ($subs, $cat) => [
                $cat => $subs->pluck('name', 'id')
                    ->map(fn ($sub) => $cat !== $sub ? "$cat — $sub" : $sub)
                    ->toArray(),
            ]);

        return $form
            ->columns(12)
            ->model(Request::class)
            ->schema([
                Group::make()
                    ->columnSpan(11)
                    ->schema([
                        Select::make('category')
                            ->options($subcategories)
                            ->required()
                            ->placeholder(null)
                            ->reactive()
                            ->searchable()
                            ->helperText(fn () => 'Choose the most relevant category for '.match ($classification) {
                                RequestClass::INQUIRY => 'your question or request for information.',
                                RequestClass::SUGGESTION => 'your idea or feedback.',
                                RequestClass::TICKET => 'the issue you are reporting.',
                            })
                            ->afterStateUpdated(function ($state, $set) use ($classification) {
                                $template = Subcategory::find($state)->{$classification->value.'Template'}()->first();

                                $set('body', $template?->content);
                            }),
                        TextInput::make('subject')
                            ->rule('required')
                            ->markAsRequired()
                            ->helperText(fn () => 'Be clear and concise about '.match ($classification) {
                                RequestClass::TICKET => 'the issue you are facing.',
                                RequestClass::SUGGESTION => 'the idea or suggestion you would like to share.',
                                RequestClass::INQUIRY => 'the question you have.',
                            }),
                        MarkdownEditor::make('body')
                            ->required()
                            ->hintAction(
                                \Filament\Forms\Components\Actions\Action::make('preview')
                                    ->modalSubmitAction(false)
                                    ->modalCancelActionLabel('Close')
                                    ->infolist(fn ($state) => [
                                        TextEntry::make('preview')
                                            ->hiddenLabel()
                                            ->state(fn () => $state)
                                            ->markdown(),
                                    ]),
                            )
                            ->helperText(fn () => 'Provide detailed information about '.match ($classification) {
                                RequestClass::INQUIRY => 'your question, specifying any necessary context for clarity.',
                                RequestClass::SUGGESTION => 'your idea, explaining its benefits and potential impact.',
                                RequestClass::TICKET => 'the issue, including any steps to reproduce it and relevant details.',
                            }),
                        FileAttachment::make(),
                    ]),
            ]);
    }

    protected static function getClassification(): RequestClass
    {
        return static::$classification ?? throw new \RuntimeException('Classification not set.');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return ['class' => static::$classification ?? null, ...$data];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $subcategory = $record->subcategories->find($data['category']);

        $category = $subcategory->category;

        abort_if(! $record->exists || ! $subcategory?->exists || ! $category?->exists, 404);

        $request = Request::make($data);

        $request->user()->associate(Auth::user());

        $request->organization()->associate($record);

        $request->category()->associate($category);

        $request->subcategory()->associate($subcategory);

        $request->from()->associate(Auth::user()->organization);

        $request->save();

        $request->actions()->create(['user_id' => Auth::id(), 'status' => ActionStatus::SUBMITTED]);

        if (count($data['files']) > 0) {
            $request->attachment()->create([
                'files' => $data['files'],
                'paths' => $data['paths'],
            ]);
        }

        $this->notifyUsers($request);

        return $request;
    }

    protected function getSavedNotificationMessage(): ?string
    {
        return 'Request submitted successfully';
    }

    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl();
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label(__('filament-panels::resources/pages/edit-record.form.actions.cancel.label'))
            ->action(fn () => $this->redirect($this->getRedirectUrl(), navigate: FilamentView::hasSpaMode() && is_app_url($this->getRedirectUrl())))
            ->color('gray');
    }
}
