<?php

namespace App\Models;

use App\Enums\ActionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'name',
        'color',
        'organization_id',
        'category_id',
        'subcategory_id',
    ];

    public static function booted()
    {
        static::addGlobalScope('non_trashed_parent', function (Builder $query) {
            $query->whereHas('organization');
        });
    }

    public function name(): Attribute
    {
        return Attribute::make(
            fn (string $name) => preg_replace('/\s+/', ' ', mb_strtolower(trim($name))),
            fn (string $name) => preg_replace('/\s+/', ' ', mb_strtolower(trim($name))),
        );
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function requests(): BelongsToMany
    {
        return $this->belongsToMany(Request::class, 'labels')
            ->using(Label::class);
    }

    public function open(): BelongsToMany
    {
        return $this->requests()
            ->whereDoesntHave('action', fn (Builder $query) => $query->where('status', ActionStatus::CLOSED));
    }

    public function closed(): BelongsToMany
    {
        return $this->requests()
            ->whereHas('action', fn (Builder $query) => $query->where('status', ActionStatus::CLOSED));
    }
}
