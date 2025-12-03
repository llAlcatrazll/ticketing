<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\Filament\AgentPanelProvider::class,
    App\Providers\Filament\AuditorPanelProvider::class,
    App\Providers\Filament\AuthPanelProvider::class,
    App\Providers\Filament\FeedbackPanelProvider::class,
    App\Providers\Filament\HomePanelProvider::class,
    App\Providers\Filament\ModeratorPanelProvider::class,
    App\Providers\Filament\RootPanelProvider::class,
    App\Providers\Filament\UserPanelProvider::class,
    App\Providers\MacroServiceProvider::class,
    Sunspikes\ClamavValidator\ClamavValidatorServiceProvider::class,
];
