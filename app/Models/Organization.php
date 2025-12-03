<?php

namespace App\Models;

use App\Filament\AvatarProviders\UiAvatarsProvider;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'address',
        'building',
        'room',
        'logo',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function logoUrl(): Attribute
    {
        return Attribute::make(fn () => $this->logo ?: (new UiAvatarsProvider)->get($this));
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function subcategories(): HasManyThrough
    {
        return $this->hasManyThrough(Subcategory::class, Category::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }
}
