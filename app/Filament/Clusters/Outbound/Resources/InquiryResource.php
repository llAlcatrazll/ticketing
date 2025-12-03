<?php

namespace App\Filament\Clusters\Outbound\Resources;

use App\Enums\RequestClass;
use App\Filament\Actions\Tables\ShowRequestAction;
use App\Filament\Actions\Tables\ViewRequestHistoryAction;
use App\Filament\Clusters\Outbound;
use App\Filament\Clusters\Outbound\Resources\RequestResource\Pages\ListInquiries;
use App\Filament\Clusters\Outbound\Resources\RequestResource\Pages\NewInquiry;
use App\Filament\Resources\RequestResource;

class InquiryResource extends RequestResource
{
    public static ?bool $inbound = false;

    protected static ?string $cluster = Outbound::class;

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
            ShowRequestAction::make(),
            ViewRequestHistoryAction::make(),
        ];
    }
}
