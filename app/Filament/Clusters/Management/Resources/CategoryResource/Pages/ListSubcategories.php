<?php

namespace App\Filament\Clusters\Management\Resources\CategoryResource\Pages;

use App\Enums\RequestClass;
use App\Filament\Actions\Tables\TemplatesPreviewActionGroup;
use App\Filament\Clusters\Management\Resources\CategoryResource;
use App\Models\Subcategory;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ListSubcategories extends ManageRelatedRecords
{
    protected static string $resource = CategoryResource::class;

    protected static string $relationship = 'subcategories';

    public function getTabs(): array
    {
        $query = fn () => $this->record->subcategories();

        return [
            'all' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed())
                ->icon('gmdi-verified-o')
                ->badge(fn () => $query()->withoutTrashed()->count()),
            'trashed' => Tab::make('Trashed')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->icon('gmdi-delete-o')
                ->badgeColor('danger')
                ->badge(fn () => $query()->onlyTrashed()->count()),
        ];
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(array_slice(parent::getBreadcrumbs(), 0, -1), [
            $this->record->name,
            'Subcategories',
            'List',
        ]);
    }

    public function getSubNavigation(): array
    {
        if (filled($cluster = static::getCluster())) {
            return $this->generateNavigationItems($cluster::getClusteredComponents());
        }

        return [];
    }

    public function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
                ->url(static::$resource::getUrl()),
            Actions\CreateAction::make()
                ->model(Subcategory::class)
                ->createAnother(false)
                ->slideOver()
                ->modalWidth(MaxWidth::Large)
                ->closeModalByClickingAway(false)
                ->form(fn (Form $form) => [
                    Forms\Components\Select::make('category_id')
                        ->columnSpanFull()
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->default($this->record->getKey())
                        ->hidden()
                        ->dehydratedWhenHidden(),
                    ...$this->form($form)->getComponents(),
                ]),
        ];
    }

    public function getHeading(): string
    {
        return "{$this->record->name} → Subcategories";
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->dehydrateStateUsing(fn (?string $state) => mb_ucfirst($state ?? ''))
                    ->rule('required')
                    ->markAsRequired()
                    ->maxLength(48),
                Forms\Components\Group::make()
                    ->relationship('inquiryTemplate')
                    ->mutateRelationshipDataBeforeCreateUsing(fn (array $data) => [...$data, 'class' => RequestClass::INQUIRY])
                    ->schema([
                        Forms\Components\MarkdownEditor::make('content')
                            ->label('Inquiry Template')
                            ->nullable(),
                    ]),
                Forms\Components\Group::make()
                    ->relationship('suggestionTemplate')
                    ->mutateRelationshipDataBeforeCreateUsing(fn (array $data) => [...$data, 'class' => RequestClass::SUGGESTION])
                    ->schema([
                        Forms\Components\MarkdownEditor::make('content')
                            ->label('Suggestion Template')
                            ->nullable(),
                    ]),
                Forms\Components\Group::make()
                    ->relationship('ticketTemplate')
                    ->mutateRelationshipDataBeforeCreateUsing(fn (array $data) => [...$data, 'class' => RequestClass::TICKET])
                    ->schema([
                        Forms\Components\MarkdownEditor::make('content')
                            ->label('Ticket Template')
                            ->nullable(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        $panel = Filament::getCurrentPanel()->getId();

        return $table
            ->heading($panel === 'root' ? "{$this->record->organization->code} → {$this->record->name} → Subcategories" : null)
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('requests_count')
                    ->label('Requests')
                    ->counts('requests'),
                Tables\Columns\TextColumn::make('open_count')
                    ->label('Open')
                    ->counts('open'),
                Tables\Columns\TextColumn::make('closed_count')
                    ->label('Closed')
                    ->counts('closed'),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\RestoreAction::make(),
                TemplatesPreviewActionGroup::make(),
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->modalWidth(MaxWidth::Large)
                    ->closeModalByClickingAway(false),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make()
                        ->modalDescription('Deleting this subcategory will affect all related records associated with it.'),
                    Tables\Actions\ForceDeleteAction::make()
                        ->modalDescription(function () {
                            $description = <<<'HTML'
                                <p class="mt-2 text-sm text-gray-500 fi-modal-description dark:text-gray-400">
                                    Deleting this subcategory will affect all related records associated with it.
                                </p>

                                <p
                                    class="mt-2 text-sm fi-modal-description text-custom-600 dark:text-custom-400"
                                    style="--c-400:var(--warning-400);--c-600:var(--warning-600);"
                                >
                                    Proceeding with this action will permanently delete the subcategory and all related records associated with it.
                                </p>
                            HTML;

                            return str($description)->toHtmlString();
                        }),
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]))
            ->groups([
                Tables\Grouping\Group::make('category.name')
                    ->label('Organization')
                    ->getDescriptionFromRecordUsing(fn (Subcategory $subcategory) => "({$subcategory->category->organization->code}) {$subcategory->category->organization->name}")
                    ->titlePrefixedWithLabel(false),
            ])
            ->groupingSettingsHidden()
            ->recordAction(null)
            ->recordUrl(null);
    }
}
