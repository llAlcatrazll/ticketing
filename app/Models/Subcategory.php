<?php

namespace App\Models;

use App\Enums\ActionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subcategory extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'name',
        'category_id',
    ];

    public static function booted()
    {
        static::addGlobalScope('non_trashed_parent', function (Builder $query) {
            $query->whereHas('category', function (Builder $query) {
                $query->whereHas('organization');
            });
        });

        static::addGlobalScope('subcategory_order', function (Builder $query) {
            $query->orderByRaw("CASE WHEN subcategories.name like 'others' THEN 1 ELSE 0 END")
                ->orderBy('subcategories.name');
        });
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }

    public function open(): HasMany
    {
        return $this->requests()
            ->whereDoesntHave('action', fn (Builder $query) => $query->where('status', ActionStatus::CLOSED));
    }

    public function closed(): HasMany
    {
        return $this->requests()
            ->whereHas('action', fn (Builder $query) => $query->where('status', ActionStatus::CLOSED));
    }

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }

    public function inquiryTemplate(): HasOne
    {
        return $this->hasOne(Template::class)->inquiry();
    }

    public function ticketTemplate(): HasOne
    {
        return $this->hasOne(Template::class)->ticket();
    }

    public function suggestionTemplate(): HasOne
    {
        return $this->hasOne(Template::class)->suggestion();
    }
}
