<?php

namespace App\Filament\Clusters\Management\Resources;

use App\Filament\Clusters\Management;
use App\Filament\Clusters\Management\Resources\TagResource\Pages\ListTags;
use App\Filament\Filters\OrganizationFilter;
use App\Models\Tag;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static ?string $navigationIcon = 'gmdi-sell-o';

    protected static ?string $cluster = Management::class;

    public static function canAccess(): bool
    {
        return in_array(Filament::getCurrentPanel()->getId(), ['root', 'admin']);
    }

    public static function form(Form $form): Form
    {
        $panel = Filament::getCurrentPanel()->getId();

        return $form
            ->schema([
                Forms\Components\Select::make('organization_id')
                    ->columnSpanFull()
                    ->relationship('organization', 'code')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->placeholder('Select organization')
                    ->visible(fn (string $operation) => $panel === 'root' && $operation === 'create'),
                Forms\Components\TextInput::make('name')
                    ->maxLength(24)
                    ->columnSpanFull()
                    ->live(debounce: 250)
                    ->rules('required')
                    ->markAsRequired()
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($get, $rule) => $rule->where('organization_id', $get('organization_id'))),
                Forms\Components\Select::make('color')
                    ->columnSpanFull()
                    ->options(array_reverse(array_combine(array_keys(Color::all()), array_map('ucfirst', array_keys(Color::all())))))
                    ->default('gray')
                    ->live(debounce: 250)
                    ->searchable()
                    ->required(),
                Forms\Components\Placeholder::make('preview')
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'w-fit'])
                    ->content(fn ($get) => new HtmlString(Blade::render(
                        '<x-filament::badge color="'.($get('color') ?? 'gray').'">'.($get('name') ?: '&lt;empty&gt;').'</x-filament::badge>'
                    ))),
            ]);
    }

    public static function table(Table $table): Table
    {
        $panel = Filament::getCurrentPanel()->getId();

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tag')
                    ->color(fn (Tag $tag) => $tag->color ?? 'gray')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('organization.code')
                    ->visible($panel === 'root')
                    ->searchable(),
                Tables\Columns\TextColumn::make('requests_count')
                    ->label('Requests')
                    ->counts('requests'),
                Tables\Columns\TextColumn::make('open_count')
                    ->label('Open')
                    ->counts('open'),
                Tables\Columns\TextColumn::make('closed_count')
                    ->label('Closed')
                    ->counts('closed'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                OrganizationFilter::make()
                    ->withUnaffiliated(false)
                    ->visible($panel === 'root'),
            ])
            ->actions([
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->modalWidth(MaxWidth::Large),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                ]),
            ])
            ->recordAction(null)
            ->recordUrl(null);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTags::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        match (Filament::getCurrentPanel()->getId()) {
            'root' => $query,
            'admin' => $query->where('organization_id', Auth::user()->organization_id),
            default => $query->whereRaw('1 = 0'),
        };

        return $query;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()
            ->withoutTrashed()
            ->count();
    }
}
