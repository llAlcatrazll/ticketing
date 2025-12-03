<?php

namespace App\Filament\Clusters\Dossiers\Resources\AllDossierResource\RelationManagers;

use App\Enums\ActionStatus;
use App\Filament\Actions\Tables\ShowRequestAction;
use App\Filament\Actions\Tables\ViewRequestHistoryAction;
use App\Models\Request;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'requests';

    protected static bool $isLazy = false;

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('code')
            ->columns([
                Tables\Columns\TextColumn::make('action.status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (Request $request) => $request->action->status === ActionStatus::CLOSED ? $request->action->resolution : $request->action->status),
                Tables\Columns\TextColumn::make('code')
                    ->extraCellAttributes(['class' => 'font-mono'])
                    ->getStateUsing(fn (Request $request) => "#{$request->code}")
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject')
                    ->searchable(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Add request')
                    ->attachAnother(false)
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['code', 'subject'])
                    ->mutateFormDataUsing(fn (array $data) => [...$data, 'user_id' => Auth::id()]),
            ])
            ->actions([
                ShowRequestAction::make(),
                ViewRequestHistoryAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DetachAction::make()
                        ->label('Remove')
                        ->modalHeading('Remove request from dossier')
                        ->modalDescription('Are you sure you want to remove this request from this dossier?'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->label('Remove')
                    ->modalHeading('Remove selected requests from dossier')
                    ->modalDescription('Are you sure you want to remove these selected requests from this dossier?'),
            ])
            ->defaultSort('requests.created_at', 'desc');
    }
}
