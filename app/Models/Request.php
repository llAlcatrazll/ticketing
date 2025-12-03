<?php

namespace App\Models;

use App\Enums\ActionStatus;
use App\Enums\RequestClass;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Request extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'class',
        'code',
        'subject',
        'body',
        'priority',
        'difficulty',
        'availability',
        'declination',
        'organization_id',
        'category_id',
        'subcategory_id',
        'user_id',
        'from_id',
    ];

    protected $casts = [
        'class' => RequestClass::class,
        'availability' => 'datetime',
        'declination' => 'boolean',
    ];

    public static function booted(): void
    {
        static::forceDeleting(function (self $request) {
            $request->attachment->delete();

            $request->actions->each->delete();
        });

        static::creating(function (self $request) {
            $faker = fake();

            do {
                $codes = collect(range(1, 10))->map(fn () => $faker->bothify('??????####'))->toArray();

                $available = array_diff($codes, self::whereIn('code', $codes)->pluck('code')->toArray());
            } while (empty($available));

            $request->code = reset($available);
        });
    }

    public function body(): Attribute
    {
        return Attribute::make(fn (string $body) => empty($body) ? null : preg_replace('/(?<!  )$/m', '  ', $body))->shouldCache();
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'assignees', 'request_id', 'assigned_id')
            ->using(Assignee::class)
            ->withTimestamps()
            ->withPivot(['response', 'responded_at', 'assigned_id', 'assigner_id']);
    }

    public function action(): HasOne
    {
        return $this->hasOne(Action::class)
            ->ofMany(['id' => 'max'], fn ($query) => $query->whereIn('status', ActionStatus::majorActions()));
    }

    public function actions(): HasMany
    {
        return $this->hasMany(Action::class)
            ->latest();
    }

    public function submitted(): HasOne
    {
        return $this->hasOne(Action::class)
            ->ofMany(['id' => 'max'], fn ($query) => $query->where('status', ActionStatus::SUBMITTED));
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function from(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'from_id');
    }

    public function assignments(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user')
            ->using(User::class)
            ->withPivot(['response', 'responded_at']);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'labels')
            ->using(Label::class)
            ->orderBy('tags.name');
    }

    public function attachment(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'attachable');
    }

    public function dossiers(): BelongsToMany
    {
        return $this->belongsToMany(Dossier::class, 'records')
            ->using(Record::class)
            ->withPivot('user_id')
            ->withTimestamps();
    }

    public function records(): HasMany
    {
        return $this->hasMany(Record::class)
            ->latest();
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class);
    }
}
