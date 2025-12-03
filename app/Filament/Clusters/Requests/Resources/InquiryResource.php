<?php

namespace App\Filament\Clusters\Requests\Resources;

use App\Enums\RequestClass;
use App\Enums\UserRole;
use App\Filament\Actions\Tables\AssignRequestAction;
use App\Filament\Actions\Tables\CloseRequestAction;
use App\Filament\Actions\Tables\CompileRequestAction;
use App\Filament\Actions\Tables\CompleteRequestAction;
use App\Filament\Actions\Tables\DeleteRequestAction;
use App\Filament\Actions\Tables\RecategorizeRequestAction;
use App\Filament\Actions\Tables\ReclassifyRequestAction;
use App\Filament\Actions\Tables\RejectRequestAction;
use App\Filament\Actions\Tables\RequeueRequestAction;
use App\Filament\Actions\Tables\RespondRequestAction;
use App\Filament\Actions\Tables\RestoreRequestAction;
use App\Filament\Actions\Tables\ShowRequestAction;
use App\Filament\Actions\Tables\TagRequestAction;
use App\Filament\Actions\Tables\ViewRequestHistoryAction;
use App\Filament\Clusters\Requests\Resources\RequestResource\Pages\ListInquiries;
use App\Filament\Clusters\Requests\Resources\RequestResource\Pages\NewInquiry;
use App\Filament\Resources\RequestResource;
use Filament\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Support\Facades\Auth;

class InquiryResource extends RequestResource
{
    public static ?RequestClass $class = RequestClass::INQUIRY;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $label = 'Inquiries';

    public static function getPages(): array
    {
        return [
            'index' => ListInquiries::route('/'),
            'new' => NewInquiry::route('new/{record}'),
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
                RespondRequestAction::make(),
                ShowRequestAction::make(),
                ViewRequestHistoryAction::make(),
                ActionGroup::make([
                    TagRequestAction::make(),
                    CompleteRequestAction::make(),
                    AssignRequestAction::make(),
                    RequeueRequestAction::make(),
                    RejectRequestAction::make(),
                    CompileRequestAction::make(),
                    RecategorizeRequestAction::make(),
                    ReclassifyRequestAction::make(),
                    CloseRequestAction::make()
                        ->requireRemarks(false),
                ]),
            ],
            UserRole::AGENT => [
                RespondRequestAction::make(),
                ShowRequestAction::make(),
                ViewRequestHistoryAction::make(),
                ActionGroup::make([
                    TagRequestAction::make(),
                    CompleteRequestAction::make(),
                    RequeueRequestAction::make(),
                    RejectRequestAction::make(),
                    CompileRequestAction::make(),
                    RecategorizeRequestAction::make(),
                    ReclassifyRequestAction::make(),
                    CloseRequestAction::make()
                        ->requireRemarks(false),
                ]),
            ],
            default => [],
        };
    }
}
