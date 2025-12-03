<?php

namespace App\Models;

use App\Enums\ActionStatus;
use App\Enums\Feedback;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'name',
        'standard_type',
        'organization_id',
        'service_type',
    ];

    protected $casts = [
        'standard_type' => Feedback::class,
        'service_type' => 'array',
    ];

    public static function booted()
    {
        static::addGlobalScope('non_trashed_parent', function (Builder $query) {
            $query->whereHas('organization');
        });

        static::addGlobalScope('category_order', function (Builder $query) {
            $query->orderByRaw("CASE WHEN categories.name like 'others' THEN 1 ELSE 0 END")
                ->orderBy('name');
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class);
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function requests(): HasManyThrough
    {
        return $this->hasManyThrough(Request::class, Subcategory::class);
    }

    public function open(): HasManyThrough
    {
        return $this->requests()
            ->whereDoesntHave('action', fn (Builder $query) => $query->where('status', ActionStatus::CLOSED));
    }

    public function closed(): HasManyThrough
    {
        return $this->requests()
            ->whereHas('action', fn (Builder $query) => $query->where('status', ActionStatus::CLOSED));
    }
}
