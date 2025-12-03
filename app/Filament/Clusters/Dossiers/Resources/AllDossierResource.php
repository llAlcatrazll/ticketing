<?php

namespace App\Filament\Clusters\Dossiers\Resources;

use App\Filament\Actions\Tables\NoteDossierAction;
use App\Filament\Clusters\Dossiers;
use App\Filament\Clusters\Dossiers\Resources\AllDossierResource\Pages;
use App\Filament\Clusters\Dossiers\Resources\AllDossierResource\RelationManagers\RequestsRelationManager;
use App\Models\Dossier;
use App\Models\Note;
use App\Models\Request;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Components\TextEntry\TextEntrySize;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AllDossierResource extends Resource
{
    protected static ?int $navigationSort = -2;

    protected static ?string $model = Dossier::class;

    protected static ?string $navigationIcon = 'gmdi-list-o';

    protected static ?string $cluster = Dossiers::class;

    protected static ?string $label = 'All';

    protected static ?string $slug = 'all';

    protected static ?string $breadcrumb = 'All';

    protected static ?string $navigationLabel = 'All';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id()),
                Forms\Components\Select::make('organization_id')
                    ->columnSpanFull()
                    ->visible(Auth::user()->root)
                    ->default(Auth::user()->organization_id)
                    ->dehydratedWhenHidden()
                    ->relationship('organization', 'name')
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\MarkdownEditor::make('description')
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\Repeater::make('records')
                    ->visibleOn('create')
                    ->columnSpanFull()
                    ->relationship()
                    ->required()
                    ->addActionLabel('Add record')
                    ->defaultItems(1)
                    ->simple(
                        Forms\Components\Select::make('request_id')
                            ->relationship(
                                'request',
                                'code',
                                fn (Builder $query) => $query->where(function ($query) {
                                    if (Auth::user()->root) {
                                        return;
                                    }

                                    $query->where('organization_id', Auth::user()->organization_id);

                                    $query->orWhere('from_id', Auth::user()->organization_id);
                                }),
                            )
                            ->searchable(['code', 'subject'])
                            ->distinct()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn (Request $request) => "#{$request->code} — {$request->subject}")
                            ->required()
                            ->validationMessages(['distinct' => 'These fields must not have a duplicate value.']),
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->limit(36)
                    ->wrap()
                    ->tooltip(fn ($column) => strlen($column->getState()) > $column->getCharacterLimit() ? $column->getState() : null),
                Tables\Columns\TextColumn::make('requests_count')
                    ->counts('requests')
                    ->label('Requests'),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable(['name', 'email']),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                NoteDossierAction::make(),
                Tables\Actions\ViewAction::make()
                    ->url(fn (Dossier $dossier, Component $livewire) => $livewire::getResource()::getUrl('show', ['record' => $dossier->id])),
            ])
            ->emptyStateHeading('No dossiers found')
            ->emptyStateDescription('Create a new dossier to make a collection of related requests.')
            ->recordAction(null);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(1)
            ->schema([
                Infolists\Components\TextEntry::make('name')
                    ->size(TextEntrySize::Large)
                    ->weight(FontWeight::Bold),
                Infolists\Components\TextEntry::make('created')
                    ->weight(FontWeight::SemiBold)
                    ->state(fn (Dossier $record) => "By {$record->user->name} on {$record->created_at->format('jS \of F Y')} at {$record->created_at->format('H:i')}"
                    ),
                Infolists\Components\TextEntry::make('description')
                    ->visible(fn (Dossier $record) => filled($record->description))
                    ->markdown(),
                Infolists\Components\RepeatableEntry::make('notes')
                    // ->contained(false)
                    ->visible(fn (Dossier $record) => $record->notes->isNotEmpty())
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->suffixAction(fn (Note $note) => Infolists\Components\Actions\Action::make('delete-'.$note->id)
                                ->requiresConfirmation()
                                ->icon('heroicon-o-trash')
                                ->color('danger')
                                ->modalHeading('Delete note')
                                ->visible(Auth::user()->is($note->user) || Auth::user()->admin)
                                ->action(function () use ($note) {
                                    $note->delete();

                                    Notification::make()
                                        ->title('Note deleted')
                                        ->success()
                                        ->send();
                                }),
                            )
                            ->getStateUsing(function (Note $note) {
                                $username = $note->user?->name ?? '<i>(non-existent user)</i>';

                                return str("<b>{$username}</b> on {$note->created_at->format('jS \of F Y')} at {$note->created_at->format('H:i')}")
                                    ->toHtmlString();
                            })
                            ->hiddenLabel(),
                        Infolists\Components\TextEntry::make('content')
                            ->hiddenLabel()
                            ->markdown(),
                        Infolists\Components\ViewEntry::make('attachment')
                            ->hiddenLabel()
                            ->visible(fn (Note $note) => $note->attachment?->exists)
                            ->view('filament.attachments.show'),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RequestsRelationManager::make(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDossiers::route('/'),
            'show' => Pages\ViewDossier::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $panel = Filament::getCurrentPanel()->getId();

        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        return match ($panel) {
            'root' => $query,
            default => $query->where('organization_id', Auth::user()->organization_id),
        };
    }
}
