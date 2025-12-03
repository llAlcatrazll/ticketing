<?php

namespace App\Filament\Resources;

use App\Enums\ActionStatus;
use App\Enums\RequestClass;
use App\Filament\Clusters\Requests;
use App\Filament\Filters\OrganizationFilter;
use App\Filament\Filters\TagFilter;
use App\Models\Request;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

abstract class RequestResource extends Resource
{
    public static ?bool $inbound = true;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = Request::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Requests::class;

    protected static ?RequestClass $class = null;

    public static function table(Table $table): Table
    {
        $panel = Filament::getCurrentPanel()->getId();

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'organization', 'action', 'actions', 'tags', 'category', 'subcategory']))
            ->columns([
                TextColumn::make('action.status')
                    ->searchable(['code'])
                    ->label('Status')
                    ->badge()
                    ->description(fn (Request $request) => "#{$request->code}")
                    ->state(function (Request $request) {
                        return match ($request->action?->status) {
                            ActionStatus::CLOSED => $request->action?->resolution,
                            ActionStatus::RESPONDED,
                            ActionStatus::STARTED => ActionStatus::IN_PROGRESS,
                            ActionStatus::SUSPENDED => ActionStatus::ON_HOLD,
                            default => $request->action?->status,
                        };
                    }),
                TextColumn::make('subject')
                    ->sortable()
                    ->searchable()
                    ->limit(24)
                    ->wrap()
                    ->tooltip(fn ($column) => strlen($column->getState()) > $column->getCharacterLimit() ? $column->getState() : null),
                TextColumn::make('user.name')
                    ->description(fn (Request $request) => $request->from?->code)
                    ->hidden(static::$inbound === null),
                TextColumn::make('organization.code')
                    ->sortable()
                    ->searchable()
                    ->limit(36)
                    ->extraCellAttributes(['class' => 'font-mono'])
                    ->tooltip(fn (Request $request) => $request->organization->name)
                    ->visible(in_array($panel, ['root']) || static::$inbound === null),
                TextColumn::make('category.name')
                    ->description(fn (Request $request) => $request->subcategory->name),
                TextColumn::make('tags.name')
                    ->badge()
                    ->wrap()
                    ->alignEnd()
                    ->toggleable()
                    ->color(fn (Request $request, string $state) => $request->tags->first(fn ($tag) => $tag->name === $state)?->color ?? 'gray'),
                TextColumn::make('created_at')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters(static::tableFilters())
            ->actions(static::tableActions())
            ->bulkActions(static::tableBulkActions())
            ->recordAction(null);
    }

    public static function getNavigationBadge(): ?string
    {
        if (Filament::getCurrentPanel()->getId() === 'root') {
            return static::getEloquentQuery()->count() ?: null;
        }

        return static::getEloquentQuery()
            ->whereHas('action', fn ($query) => $query->where('status', ActionStatus::SUBMITTED))
            ->count() ?: null;
    }

    public static function getEloquentQuery(): Builder
    {
        $panel = Filament::getCurrentPanel()->getId();

        $query = parent::getEloquentQuery()
            ->when(static::$class, fn ($query, $class) => $query->where('class', $class))
            ->when($panel !== 'root' && static::$inbound !== null, fn ($query) => $query->whereHas('action', fn ($query) => $query->where('status', '!=', ActionStatus::RECALLED)));

        return match (static::$inbound) {
            true => match ($panel) {
                'root' => $query,
                'admin' => $query->where('organization_id', Auth::user()->organization_id),
                'moderator' => $query->where('organization_id', Auth::user()->organization_id),
                'agent' => $query->whereHas('assignees', fn ($query) => $query->where('assigned_id', Auth::id())),
                default => $query->whereRaw('1 = 0'),
            },
            false => match ($panel) {
                'root' => $query,
                'admin','moderator' => $query->where('from_id', Auth::user()->organization_id),
                default => $query->whereRaw('1 = 0'),
            },
            default => $query->where('user_id', Auth::id()),
        };
    }

    public static function tableFilters(): array
    {
        if (static::class === self::class) {
            return [
                SelectFilter::make('class')
                    ->options(RequestClass::class),
            ];
        }

        return match (Filament::getCurrentPanel()->getId()) {
            'root' => [
                TagFilter::make(),
                OrganizationFilter::make()
                    ->withUnaffiliated(false),
                TrashedFilter::make(),
            ],
            default => [
                TagFilter::make(),
            ],
        };
    }

    abstract public static function tableActions(): array;

    public static function tableBulkActions(): array
    {
        return [];
    }
}
