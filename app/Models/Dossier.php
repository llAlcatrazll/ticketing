<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dossier extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'organization_id',
        'user_id',
    ];

    public static function booted(): void
    {
        static::forceDeleting(fn (self $dossier) => $dossier->notes->each->delete());
    }

    public function name(): Attribute
    {
        return Attribute::make(
            get: fn ($name) => mb_ucfirst($name),
            set: fn ($name) => mb_ucfirst($name),
        );
    }

    public function description(): Attribute
    {
        return Attribute::make(
            get: fn (string $description) => preg_replace('/(?<!  )$/m', '  ', $description),
            set: fn (?string $description) => preg_replace('/[^\S\r\n]+$/m', '', $description ?? ''),
        )->shouldCache();
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function requests(): BelongsToMany
    {
        return $this->belongsToMany(Request::class, 'records')
            ->using(Record::class)
            ->withPivot('user_id')
            ->withTimestamps();
    }

    public function records(): HasMany
    {
        return $this->hasMany(Record::class);
    }

    public function case(): HasOne
    {
        return $this->hasOne(Record::class)
            ->ofMany();
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
