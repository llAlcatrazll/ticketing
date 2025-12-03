<?php

namespace App\Filament\Clusters\Personal\Resources;

use App\Enums\RequestClass;
use App\Filament\Actions\Tables\CancelRequestAction;
use App\Filament\Actions\Tables\CompileRequestAction;
use App\Filament\Actions\Tables\RecallRequestAction;
use App\Filament\Actions\Tables\ReopenRequestAction;
use App\Filament\Actions\Tables\ResubmitRequestAction;
use App\Filament\Actions\Tables\ShowRequestAction;
use App\Filament\Actions\Tables\UndoRecentAction;
use App\Filament\Actions\Tables\UpdateRequestAction;
use App\Filament\Actions\Tables\ViewRequestHistoryAction;
use App\Filament\Clusters\Personal;
use App\Filament\Clusters\Personal\Resources\RequestResource\Pages\ListSuggestions;
use App\Filament\Clusters\Personal\Resources\RequestResource\Pages\NewSuggestion;
use App\Filament\Resources\RequestResource;
use Filament\Tables\Actions\ActionGroup;

class SuggestionResource extends RequestResource
{
    public static ?bool $inbound = null;

    protected static ?string $cluster = Personal::class;

    public static ?RequestClass $class = RequestClass::SUGGESTION;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?string $label = 'Suggestions';

    public static function getPages(): array
    {
        return [
            'index' => ListSuggestions::route('/'),
            'new' => NewSuggestion::route('new/{record}'),
        ];
    }

    public static function tableActions(): array
    {
        return [
            ResubmitRequestAction::make(),
            ShowRequestAction::make(),
            ViewRequestHistoryAction::make(),
            ActionGroup::make([
                UndoRecentAction::make(),
                ReopenRequestAction::make(),
                UpdateRequestAction::make(),
                RecallRequestAction::make(),
                CancelRequestAction::make(),
                CompileRequestAction::make(),
            ]),
        ];
    }
}
