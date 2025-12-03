<?php

namespace App\Filament\Clusters\Requests\Resources\RequestResource\Pages;

use App\Filament\Actions\NewRequestPromptAction;
use App\Filament\Clusters\Requests\Resources\InquiryResource;
use App\Filament\Concerns\HasInquiryTabs;
use Filament\Resources\Pages\ListRecords;

class ListInquiries extends ListRecords
{
    use HasInquiryTabs;

    protected static string $resource = InquiryResource::class;

    public function getHeaderActions(): array
    {
        return [
            NewRequestPromptAction::make()
                ->class(static::getResource()::$class),
        ];
    }
}
