<?php

namespace App\Filament\Clusters\Management\Resources;

use App\Enums\UserRole;
use App\Filament\Actions\Tables\ApproveAccountAction;
use App\Filament\Actions\Tables\DeactivateAccessAction;
use App\Filament\Clusters\Management;
use App\Filament\Clusters\Management\Resources\UserResource\Pages;
use App\Filament\Filters\OrganizationFilter;
use App\Filament\Filters\RoleFilter;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?int $navigationSort = -3;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'gmdi-supervised-user-circle-o';

    protected static ?string $cluster = Management::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Account';

    public static function canAccess(): bool
    {
        return in_array(Filament::getCurrentPanel()->getId(), ['root', 'admin']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Forms\Components\FileUpload::make('avatar')
                    ->avatar()
                    ->alignCenter()
                    ->directory('avatars'),
                Forms\Components\TextInput::make('name')
                    ->unique(ignoreRecord: true)
                    ->markAsRequired()
                    ->rule('required')
                    ->prefixIcon('heroicon-o-user-circle'),
                Forms\Components\TextInput::make('designation')
                    ->prefixIcon('heroicon-o-briefcase'),
                Forms\Components\Select::make('organization_id')
                    ->relationship('organization', 'name')
                    ->visible(Filament::getCurrentPanel()->getId() === 'root')
                    ->prefixIcon('gmdi-business'),
                Forms\Components\Select::make('role')
                    ->options(UserRole::options(Auth::user()->root))
                    ->prefixIcon('gmdi-shield-o')
                    ->default('user')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->rules(['email', 'required'])
                    ->unique(ignoreRecord: true)
                    ->markAsRequired()
                    ->prefixIcon('heroicon-o-at-symbol'),
                Forms\Components\TextInput::make('number')
                    ->label('Number')
                    ->placeholder('9xx xxx xxxx')
                    ->mask('999 999 9999')
                    ->prefixIcon('heroicon-o-phone')
                    ->rule(fn () => function ($a, $v, $f) {
                        if (! preg_match('/^9.*/', $v)) {
                            $f('The mobile number field must follow a format of 9xx-xxx-xxxx.');
                        }
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        $panel = Filament::getCurrentPanel()->getId();

        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular()
                    ->extraImgAttributes(['loading' => 'lazy'])
                    ->grow(false),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('organization.code')
                    ->visible($panel === 'root')
                    ->searchable(isIndividual: true)
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->searchable(),
                Tables\Columns\TextColumn::make('approvedBy.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deactivatedBy.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deactivated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->label('Affiliated organization')
                    ->visible($panel === 'root'),
                RoleFilter::make(),
            ])
            ->actions([
                ApproveAccountAction::make()
                    ->label('Approve'),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->modalWidth(MaxWidth::Medium),
                Tables\Actions\ActionGroup::make([
                    DeactivateAccessAction::make()
                        ->label(fn (User $user) => $user->deactivated_at ? 'Reactivate' : 'Deactivate'),
                    Tables\Actions\DeleteAction::make()
                        ->visible($panel === 'root'),
                    Tables\Actions\ForceDeleteAction::make()
                        ->visible($panel === 'root'),
                ]),
            ])
            ->recordAction(null)
            ->recordUrl(null);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->whereNot('id', Auth::id());

        return match (Filament::getCurrentPanel()->getId()) {
            'root' => $query
                ->withoutGlobalScopes([
                    SoftDeletingScope::class,
                ]),
            'admin' => $query->whereNot('role', UserRole::ROOT)
                ->whereNotNull('organization_id')
                ->where('organization_id', Auth::user()->organization_id),
            default => $query->whereRaw('1 = 0'),
        };
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }
}
