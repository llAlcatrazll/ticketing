<?php

namespace App\Filament\Clusters\Dossiers\Resources;

use App\Enums\ActionStatus;
use App\Filament\Clusters\Dossiers;
use App\Filament\Clusters\Dossiers\Resources\OpenDossierResource\Pages;
use App\Models\Dossier;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class OpenDossierResource extends Resource
{
    protected static ?int $navigationSort = -2;

    protected static ?string $model = Dossier::class;

    protected static ?string $navigationIcon = 'gmdi-circle-o';

    protected static ?string $cluster = Dossiers::class;

    protected static ?string $label = 'Open';

    protected static ?string $slug = 'open';

    protected static ?string $breadcrumb = 'Open';

    protected static ?string $navigationLabel = 'Open';

    public static function form(Form $form): Form
    {
        return AllDossierResource::form($form);
    }

    public static function table(Table $table): Table
    {
        return AllDossierResource::table($table);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return AllDossierResource::infolist($infolist);
    }

    public static function getRelations(): array
    {
        return AllDossierResource::getRelations();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOpenDossiers::route('/'),
            'show' => Pages\ViewDossier::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $panel = Filament::getCurrentPanel()->getId();

        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where(function ($query) {
                $query->whereHas('requests', function (Builder $query) {
                    $query->whereRelation('action', 'status', '!=', ActionStatus::CLOSED);
                });
            });

        return match ($panel) {
            'root' => $query,
            default => $query->where('organization_id', Auth::user()->organization_id),
        };
    }
}
