<?php

namespace App\Filament\Panels\Feedback\Pages;

use App\Models\Organization;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Pages\SimplePage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

class ThankYou extends SimplePage
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.panels.feedback.pages.thank-you';

    public static function registerRoutes() : void
    {
        Route::get('{organization}/thank-you', static::class)->name('thank-you');
    }
}
