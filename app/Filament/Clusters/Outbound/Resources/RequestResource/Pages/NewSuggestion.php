<?php

namespace App\Filament\Clusters\Outbound\Resources\RequestResource\Pages;

use App\Enums\RequestClass;
use App\Filament\Clusters\Outbound\Resources\SuggestionResource;
use App\Filament\Concerns\NewRequest;
use Filament\Resources\Pages\EditRecord;

class NewSuggestion extends EditRecord
{
    use NewRequest;

    public static RequestClass $classification = RequestClass::SUGGESTION;

    protected static string $resource = SuggestionResource::class;

    protected static ?string $breadcrumb = 'New Suggestion';
}
