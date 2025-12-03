<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Filament\AvatarProviders\UiAvatarsProvider;
use App\Models\Scopes\ActiveScope;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

#[ScopedBy([ActiveScope::class])]
class User extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasUlids, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'number',
        'designation',
        'role',
        'purpose',
        'organization_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'deactivated_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function active(): Attribute
    {
        return Attribute::make(
            fn (): bool => $this->deactivated_at === null,
            fn (bool $active, array $attributes): array => ['deactivated_at' => $active ? null : $attributes['deactivated_at'] ?? now()],
        )->shouldCache();
    }

    public function emailVerifiedAt(): Attribute
    {
        return Attribute::make(
            fn (): ?Carbon => $this->verified_at,
            fn (Carbon|bool|null $timestamp): array => ['verified_at' => $timestamp],
        )->shouldCache();
    }

    public function number(): Attribute
    {
        return Attribute::make(
            // get: fn (?string $number): ?string => empty($number) ? null : str($number)->replaceMatches('/(\d{3})(\d{3})(\d{4})/', '$1 $2 $3')->prepend('+63 '),
            set: fn (?string $number): array => ['number' => empty($number) ? null : str($number)->replaceMatches('/\s+|^\+63/', '')],
        )->shouldCache();
    }

    public function purpose(): Attribute
    {
        $sanitize = function ($purpose): string {
            return preg_replace(
                '/(\r?\n\s*)+\r?\n/',
                "\n\n",
                implode("\n", array_map(fn ($line) => preg_replace('/\s+/', ' ', trim($line)), preg_split('/\r?\n/', $purpose)))
            );
        };

        return Attribute::make(
            fn (?string $purpose): ?string => empty($purpose) ? null : $sanitize($purpose),
            fn (?string $purpose): array => ['purpose' => empty($purpose) ? null : $sanitize($purpose)],
        )->shouldCache();
    }

    public function root(): Attribute
    {
        return Attribute::make(fn (): bool => $this->role === UserRole::ROOT);
    }

    public function admin(): Attribute
    {
        return Attribute::make(fn (): bool => $this->role === UserRole::ADMIN);
    }

    public function avatarUrl(): Attribute
    {
        return Attribute::make(fn (): string => $this->avatar ?? (new UiAvatarsProvider)->get($this));
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function assignments(): BelongsToMany
    {
        return $this->belongsToMany(Request::class, 'assignee')
            ->using(Assignee::class)
            ->withPivot(['response', 'responded_at']);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }

    public function actions(): HasMany
    {
        return $this->hasMany(Action::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function deactivatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deactivated_by');
    }

    public function scopeUser(Builder $query): Builder
    {
        return $query->where('role', UserRole::USER);
    }

    public function scopeAgent(Builder $query, bool $moderators = false, bool $admin = false): Builder
    {
        return $query->where(function ($query) use ($moderators, $admin) {
            $query->where('role', UserRole::AGENT);

            $query->when($moderators, fn ($query) => $query->orWhere('role', UserRole::MODERATOR));

            $query->when($admin, fn ($query) => $query->orWhere('role', UserRole::ADMIN));
        });
    }

    public function scopeModerator(Builder $query, bool $admin = false): Builder
    {
        return $query->where(function ($query) use ($admin) {
            $query->where('role', UserRole::MODERATOR);

            $query->when($admin, fn ($query) => $query->orWhere('role', UserRole::ADMIN));
        });
    }

    public function scopeAdmin(Builder $query): Builder
    {
        return $query->where('role', UserRole::ADMIN);
    }

    public function scopeRoot(Builder $query): Builder
    {
        return $query->where('role', UserRole::ROOT);
    }

    public function scopeSortByRole(Builder $query, bool $ascending = true): Builder
    {
        $roles = UserRole::cases();

        $placeholders = implode(',', array_fill(0, count($roles), '?'));

        return $query->orderByRaw("FIELD(role, {$placeholders}) ".($ascending ? 'ASC' : 'DESC'), $roles);
    }

    public function scopeForApproval(Builder $query): Builder
    {
        return $query->whereNull('approved_at')->whereNotNull('verified_at');
    }

    public function scopeForVerification(Builder $query): Builder
    {
        return $query->whereNull('verified_at');
    }

    public function scopeVerifiedEmail(Builder $query): Builder
    {
        return $query->whereNotNull('verified_at');
    }

    public function scopeApprovedAccount(Builder $query): Builder
    {
        return $query->verifiedEmail()->whereNotNull('approved_at');
    }

    public function reactivate(): void
    {
        $this->forceFill([
            'deactivated_by' => null,
            'deactivated_at' => null,
        ])->save();
    }

    public function deactivate(self $user): void
    {
        $this->forceFill([
            'deactivated_by' => $user->id,
            'deactivated_at' => now(),
        ])->save();
    }

    public function hasApprovedAccount(): bool
    {
        return isset($this->approved_at);
    }

    public function hasActiveAccess(): bool
    {
        return is_null($this->deactivated_at);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($panel->getId(), ['auth', 'user']) ?: $this->role?->value === $panel->getId();
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url;
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }
}
