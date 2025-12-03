<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Note extends Model
{
    use HasUlids;

    protected $fillable = [
        'content',
        'notable_id',
        'notable_type',
        'user_id',
    ];

    public static function booted(): void
    {
        static::deleting(fn (self $note) => $note->attachment?->delete());
    }

    public function content(): Attribute
    {
        return Attribute::make(
            get: fn (string $content) => preg_replace('/(?<!  )$/m', '  ', $content),
            set: fn (?string $content) => preg_replace('/[^\S\r\n]+$/m', '', $content ?? ''),
        )->shouldCache();
    }

    public function notable(): MorphTo
    {
        return $this->morphTo();
    }

    public function attachment(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'attachable');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
