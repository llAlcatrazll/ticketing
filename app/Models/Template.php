<?php

namespace App\Models;

use App\Enums\RequestClass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Template extends Model
{
    use HasUlids;

    protected $fillable = [
        'class',
        'content',
        'subcategory_id',
    ];

    protected $casts = [
        'class' => RequestClass::class,
    ];

    public function content(): Attribute
    {
        return Attribute::make(
            fn (?string $content): ?string => preg_replace('/(?<!  )$/m', '  ', $content ?? ''),
            fn (?string $content): ?string => preg_replace('/[^\S\r\n]+$/m', '', $content ?? ''),
        )->shouldCache();
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function scopeClass(Builder $query, RequestClass $class): Builder
    {
        return $query->where('class', $class);
    }

    public function scopeInquiry(Builder $query): Builder
    {
        return $query->class(RequestClass::INQUIRY);
    }

    public function scopeTicket(Builder $query): Builder
    {
        return $query->class(RequestClass::TICKET);
    }

    public function scopeSuggestion(Builder $query): Builder
    {
        return $query->class(RequestClass::SUGGESTION);
    }
}
