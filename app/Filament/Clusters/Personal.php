<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Filament\Facades\Filament;

class Personal extends Cluster
{
    protected static ?int $navigationSort = PHP_INT_MAX;

    protected static ?string $navigationIcon = 'gmdi-support-agent-o';

    public static function getNavigationLabel(): string
    {
        return Filament::getCurrentPanel()->getId() === 'user' ? 'Requests' : parent::getNavigationLabel();
    }
}
