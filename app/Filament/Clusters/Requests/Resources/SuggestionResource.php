<?php

namespace App\Filament\Clusters\Requests\Resources;

use App\Enums\RequestClass;
use App\Enums\UserRole;
use App\Filament\Actions\Tables\AcknowledgeRequestAction;
use App\Filament\Actions\Tables\AssignRequestAction;
use App\Filament\Actions\Tables\CompileRequestAction;
use App\Filament\Actions\Tables\DeleteRequestAction;
use App\Filament\Actions\Tables\InvalidateRequestAction;
use App\Filament\Actions\Tables\RecategorizeRequestAction;
use App\Filament\Actions\Tables\ReclassifyRequestAction;
use App\Filament\Actions\Tables\RejectRequestAction;
use App\Filament\Actions\Tables\RestoreRequestAction;
use App\Filament\Actions\Tables\ShowRequestAction;
use App\Filament\Actions\Tables\TagRequestAction;
use App\Filament\Actions\Tables\ViewRequestHistoryAction;
use App\Filament\Clusters\Requests\Resources\RequestResource\Pages\ListSuggestions;
use App\Filament\Clusters\Requests\Resources\RequestResource\Pages\NewSuggestion;
use App\Filament\Resources\RequestResource;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ForceDeleteAction;
use Illuminate\Support\Facades\Auth;

class SuggestionResource extends RequestResource
{
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
        return match (Auth::user()->role) {
            UserRole::ROOT => [
                ShowRequestAction::make()
                    ->hidden(false),
                ViewRequestHistoryAction::make()
                    ->hidden(false),
                ActionGroup::make([
                    CompileRequestAction::make(),
                    RestoreRequestAction::make(),
                    RecategorizeRequestAction::make(),
                    ReclassifyRequestAction::make(),
                    DeleteRequestAction::make(),
                    ForceDeleteAction::make()
                        ->label('Purge'),
                ]),
            ],
            UserRole::ADMIN, UserRole::MODERATOR => [
                AcknowledgeRequestAction::make(),
                AssignRequestAction::make(),
                ShowRequestAction::make(),
                ViewRequestHistoryAction::make(),
                ActionGroup::make([
                    TagRequestAction::make(),
                    CompileRequestAction::make(),
                    RecategorizeRequestAction::make(),
                    ReclassifyRequestAction::make(),
                    InvalidateRequestAction::make(),
                ]),
            ],
            UserRole::AGENT => [
                AcknowledgeRequestAction::make(),
                ShowRequestAction::make(),
                ViewRequestHistoryAction::make(),
                ActionGroup::make([
                    TagRequestAction::make(),
                    RejectRequestAction::make(),
                    CompileRequestAction::make(),
                    RecategorizeRequestAction::make(),
                    ReclassifyRequestAction::make(),
                    InvalidateRequestAction::make(),
                ]),
            ],
            default => [],
        };
    }
}
