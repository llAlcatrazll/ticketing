<?php

namespace App\Filament\Officer\Resources;

use App\Enums\RequestStatus;
use App\Filament\Actions\Tables\ApproveRequestAction;
use App\Filament\Actions\Tables\AssignRequestAction;
use App\Filament\Actions\Tables\DeclineRequestAction;
use App\Filament\Actions\Tables\ResolveRequestAction;
use App\Filament\Actions\Tables\ViewRequestHistoryAction;
use App\Filament\Officer\Resources\RequestResource\Pages;
use App\Models\Category;
use App\Models\Request;
use App\Models\Subcategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid as ComponentsGrid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class RequestResource extends Resource
{
    protected static ?string $model = Request::class;

    public static function form(Form $form): Form
    {
        return $form
            ->columns(12)
            ->schema([
                Forms\Components\Section::make('Request')
                    ->columnSpan(8)
                    ->columns(2)
                    ->compact()
                    ->schema([
                        Forms\Components\Select::make('office_id')
                            ->relationship('office', 'name')
                            ->columnSpan(2)
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('category_id', null) | $set('subcategory_id', null))
                            ->required(),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name', fn (Builder $query, Forms\Get $get) => $query->where('office_id', $get('office_id')))
                            ->reactive()
                            ->preload()
                            ->searchable()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('subcategory_id', null))
                            ->required(),
                        Forms\Components\Select::make('subcategory_id')
                            ->relationship('subcategory', 'name', fn (Builder $query, Forms\Get $get) => $query->where('category_id', $get('category_id')))
                            ->reactive()
                            ->preload()
                            ->searchable()
                            ->required(),
                        Forms\Components\TextInput::make('subject')
                            ->columnSpan(2)
                            ->placeholder('Enter the subject of the request')
                            ->markAsRequired()
                            ->rule('required')
                            ->maxLength(255),
                        Forms\Components\RichEditor::make('remarks')
                            ->columnSpan(2)
                            ->label('Remarks')
                            ->placeholder('Provide a detailed description of the issue to ensure that the assigned personnel have a comprehensive understanding of the problem, which will help them address it more effectively.')
                            ->hidden(fn (string $operation, ?string $state) => $operation === 'view' && $state === null)
                            ->required(),
                    ]),
                Forms\Components\Group::make()
                    ->columnSpan(4)
                    ->schema([
                        Forms\Components\Section::make('Availability')
                            ->description(fn (string $operation) => $operation !== 'view' ? 'Set your availability date for the request. Leave these fields blank if not necessary.' : null)
                            ->hidden(fn (string $operation, ?Request $record) => $operation === 'view' && $record?->availability_from === null && $record?->availability_to === null)
                            ->compact()
                            ->columns(2)
                            ->collapsed(fn (string $operation) => $operation !== 'view')
                            ->schema([
                                Forms\Components\DatePicker::make('availability_from')
                                    ->label('From')
                                    ->seconds(false)
                                    ->hidden(fn (string $operation, ?string $state) => $operation === 'view' && $state === null),
                                Forms\Components\DatePicker::make('availability_to')
                                    ->label('Until')
                                    ->seconds(false)
                                    ->afterOrEqual('availability_from')
                                    ->hidden(fn (string $operation, ?string $state) => $operation === 'view' && $state === null),
                            ]),
                        Forms\Components\Repeater::make('attachments')
                            ->relationship('attachment')
                            ->label('Attachments')
                            ->columnSpanFull()
                            ->deletable(false)
                            ->addable(false)
                            ->hidden(fn (?Request $record, string $operation) => $operation === 'view' && $record?->attachment()->first()->empty)
                            ->hint(fn (string $operation) => $operation !== 'view' ? 'Help' : null)
                            ->hintIcon(fn (string $operation) => $operation !== 'view' ? 'heroicon-o-question-mark-circle' : null)
                            ->hintIconTooltip('Please upload a maximum file count of 5 items and file size of 4096 kilobytes.')
                            ->helperText(fn (string $operation) => $operation !== 'view' ? 'If necessary, you may upload files that will help the assigned personnel better understand the issue.' : null)
                            ->simple(fn (?Request $record) => Forms\Components\FileUpload::make('paths')
                                ->placeholder(fn (string $operation) => match ($operation) {
                                    'view' => 'Click the icon at the left side of the filename to download',
                                    default => null,
                                })
                                ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, string $operation) use ($record) {
                                    return $operation === 'create'
                                        ? str(str()->ulid())
                                            ->lower()
                                            ->append('.'.$file->getClientOriginalExtension())
                                        : str(str()->ulid())
                                            ->prepend("request-{$record->id}-")
                                            ->lower()
                                            ->append(".{$file->getClientOriginalExtension()}");
                                })
                                ->directory('attachments')
                                ->storeFileNamesIn('files')
                                ->multiple()
                                ->maxFiles(5)
                                ->downloadable()
                                ->previewable(false)
                                ->maxSize(1024 * 4)
                                ->removeUploadedFileButtonPosition('right')
                            )
                            ->rule(fn () => function ($attribute, $value, $fail) {
                                $files = collect(current($value)['paths'])->map(function (TemporaryUploadedFile|string $file) use ($value) {
                                    return [
                                        'file' => $file instanceof TemporaryUploadedFile
                                            ? $file->getClientOriginalName()
                                            : current($value)['files'][$file],
                                        'hash' => $file instanceof TemporaryUploadedFile
                                            ? hash_file('sha512', $file->getRealPath())
                                            : hash_file('sha512', storage_path("app/public/$file")),
                                    ];
                                });

                                if (($duplicates = $files->duplicates('hash'))->isNotEmpty()) {
                                    $dupes = $files->filter(fn ($file) => $duplicates->contains($file['hash']))->unique();

                                    $fail('Please do not upload the same files ('.$dupes->map->file->join(', ').') multiple times.');
                                }
                            }),
                        Forms\Components\Fieldset::make('Tags')
                            ->columns(1)
                            ->hidden(fn (string $operation, ?Request $record) => $operation === 'view' && $record?->tags->isEmpty())
                            ->schema([
                                Forms\Components\CheckboxList::make('tags')
                                    ->hint(fn (string $operation) => $operation !== 'view' ? 'Select tags that best describe the request issue. This will help in categorizing the request.' : '')
                                    ->hiddenLabel()
                                    ->reactive()
                                    ->columns(2)
                                    ->relationship(titleAttribute: 'name', modifyQueryUsing: function (Builder $query, Forms\Get $get) {
                                        $query->orWhere(function (Builder $query) use ($get) {
                                            $query->where('taggable_type', Category::class);

                                            $query->where('taggable_id', $get('category_id'));
                                        });
                                        $query->orWhere(function (Builder $query) use ($get) {
                                            $query->where('taggable_type', Subcategory::class);

                                            $query->where('taggable_id', $get('subcategory_id'));
                                        });
                                    })
                                    ->searchable(),
                            ]),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->whereHas('action', function (Builder $query) {
                    $query->whereNot('status', RequestStatus::RETRACTED);
                });

                $query->where('office_id', Auth::user()->office_id);

            })
            ->columns([
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(24)
                    ->tooltip(fn (Request $record) => $record->subject),
                Tables\Columns\TextColumn::make('office.acronym')
                    ->sortable()
                    ->searchable()
                    ->limit(12)
                    ->tooltip(fn (Request $record) => $record->office->name),
                Tables\Columns\TextColumn::make('requestor.name')
                    ->sortable()
                    ->searchable()
                    ->limit(24)
                    ->tooltip(fn (Request $record) => $record->requestor->name),
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->limit(36)
                    ->formatStateUsing(fn ($record) => "{$record->category->name} ({$record->subcategory->name})")
                    ->tooltip(fn (Request $record) => "{$record->category->name} ({$record->subcategory->name})"),
                Tables\Columns\TextColumn::make('action.status')
                    ->searchable()
                    ->label('Status')
                    ->tooltip(fn (Request $record) => $record->action->status->getDescription())
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->modalWidth('7xl')
                    ->infolist([
                        ComponentsGrid::make(12)
                            ->schema([
                                Group::make([
                                    Section::make('Personal Details')
                                        ->columnSpan(8)
                                        ->columns(3)
                                        ->schema([
                                            TextEntry::make('requestor.name')
                                                ->label('Name'),
                                            TextEntry::make('requestor.number')
                                                ->prefix('+63 0')
                                                ->label('Phone Number'),
                                            TextEntry::make('requestor.email')
                                                ->label('Email'),
                                        ]),
                                    Section::make('Office Details')
                                        ->columnSpan(8)
                                        ->columns(3)
                                        ->schema([
                                            TextEntry::make('office.acronym')
                                                ->label('Office'),
                                            TextEntry::make('office.room')
                                                ->label('Room Number'),
                                            TextEntry::make('office.address')
                                                ->label('Office address :'),

                                        ]),

                                ])->columnSpan(8),

                                Group::make([
                                    Section::make('Availability')
                                        ->columnSpan(4)
                                        ->columns(2)
                                        ->schema([
                                            TextEntry::make('availability_from')
                                                ->columnSpan(1)
                                                ->date()
                                                ->label('Availability from'),
                                            TextEntry::make('availability_to')
                                                ->columnSpan(1)
                                                ->date()
                                                ->label('Availability to'),

                                        ]),
                                    Section::make('Assignees')
                                        ->columnSpan(4)
                                        ->schema([
                                            TextEntry::make('')
                                                ->label(false)
                                                ->placeholder(fn ($record) => implode(', ', $record->assignees->pluck('name')->toArray()))
                                                ->inLinelabel(false),
                                        ]),

                                ])->columnSpan(4),
                                Section::make('Request Details')
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('category.name')
                                            ->label('Category'),
                                        TextEntry::make('subcategory.name')
                                            ->label('Subcategory'),
                                    ])->columnSpan(4),

                                Group::make([

                                    Section::make('Attachments')
                                        ->columns(2)
                                        ->schema(function ($record) {
                                            return [
                                                TextEntry::make('attachment.attachable_id')
                                                    ->formatStateUsing(function ($record) {
                                                        $attachments = json_decode($record->attachment->files, true);

                                                        $html = collect($attachments)->map(function ($filename, $path) {
                                                            $fileName = basename($path);
                                                            $fileUrl = Storage::url($path);

                                                            return "<a href='{$fileUrl}' download='{$fileName}'>{$filename}</a>";
                                                        })->implode('<br>');

                                                        return $html;
                                                    })
                                                    ->openUrlInNewTab()
                                                    ->label(false)
                                                    ->inLineLabel(false)
                                                    ->html(),
                                            ];
                                        }),
                                ])->columnSpan(4),
                                Group::make([
                                    Section::make('Request Rating')
                                        ->columnSpan(4)
                                        ->visible(fn ($record) => in_array(RequestStatus::RESOLVED, $record->actions->pluck('status')->toArray()))
                                        ->schema([
                                            TextEntry::make('remarks')
                                                ->markdown()
                                                ->label(false),
                                        ]),

                                ])->columnSpan(4),
                                Section::make('Remarks')
                                    ->columnSpan(12)
                                    ->schema([
                                        TextEntry::make('remarks')
                                            ->columnSpan(2)
                                            ->formatStateUsing(fn ($record) => new HtmlString($record->remarks))
                                            ->label(false),
                                    ]),
                            ]),
                        Group::make([

                        ])->columnSpan(4),

                    ]),
                ActionGroup::make([
                    AssignRequestAction::make(),
                    ApproveRequestAction::make(),
                    DeclineRequestAction::make(),
                    ResolveRequestAction::make(),
                    ViewRequestHistoryAction::make(),
                ]),
            ])
            ->recordAction(null)
            ->recordUrl(null);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRequests::route('/'),
        ];
    }
}
