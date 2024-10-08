<?php

namespace App\Filament\Admin\Resources;

use App\Enums\RequestStatus;
use App\Filament\Actions\Tables\ViewRequestHistoryAction;
use App\Filament\Admin\Resources\RequestResource\Pages;
use App\Models\Request;
use Filament\Infolists\Components\Grid as ComponentsGrid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class RequestResource extends Resource
{
    protected static ?string $model = Request::class;

    protected static ?int $navigationSort = -100;

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->whereHas('action');
                // filter only published requests
            })
            ->columns([
                Tables\Columns\TextColumn::make('requestor.name')
                    ->label('Requestor Name')
                    ->searchable()
                    ->sortable()
                    ->limit(13),
                Tables\Columns\TextColumn::make('office.acronym')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('action.status')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('office')
                    ->relationship('office', 'acronym')
                    ->searchable()
                    ->preload(),
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

                    ]),
                ViewRequestHistoryAction::make(),
            ]
            );

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
