<?php

namespace App\Filament\Clusters\Personal\Resources;

use App\Enums\RequestClass;
use App\Filament\Actions\Tables\CancelRequestAction;
use App\Filament\Actions\Tables\CompileRequestAction;
use App\Filament\Actions\Tables\RecallRequestAction;
use App\Filament\Actions\Tables\ReopenRequestAction;
use App\Filament\Actions\Tables\ResolveRequestAction;
use App\Filament\Actions\Tables\RespondRequestAction;
use App\Filament\Actions\Tables\ResubmitRequestAction;
use App\Filament\Actions\Tables\ShowRequestAction;
use App\Filament\Actions\Tables\UndoRecentAction;
use App\Filament\Actions\Tables\UpdateRequestAction;
use App\Filament\Actions\Tables\ViewRequestHistoryAction;
use App\Filament\Clusters\Personal;
use App\Filament\Clusters\Personal\Resources\RequestResource\Pages\ListInquiries;
use App\Filament\Clusters\Personal\Resources\RequestResource\Pages\NewInquiry;
use App\Filament\Resources\RequestResource;
use Filament\Tables\Actions\ActionGroup;

class InquiryResource extends RequestResource
{
    public static ?bool $inbound = null;

    protected static ?string $cluster = Personal::class;

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
        return [
            RespondRequestAction::make(),
            ResubmitRequestAction::make(),
            ResolveRequestAction::make(),
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
