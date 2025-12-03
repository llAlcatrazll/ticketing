<?php

namespace App\Filament\Clusters\Personal\Resources\RequestResource\Pages;

use App\Enums\RequestClass;
use App\Filament\Clusters\Personal\Resources\InquiryResource;
use App\Filament\Concerns\NewRequest;
use Filament\Resources\Pages\EditRecord;

class NewInquiry extends EditRecord
{
    use NewRequest;

    public static RequestClass $classification = RequestClass::INQUIRY;

    protected static string $resource = InquiryResource::class;

    protected static ?string $breadcrumb = 'New Inquiry';

    public function getSubNavigation(): array
    {
        if (filled($cluster = static::getCluster())) {
            return $this->generateNavigationItems($cluster::getClusteredComponents());
        }

        return [];
    }
}
