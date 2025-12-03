<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasUlids;

    public static $purgable = [
        Action::class => 90,
        Request::class => 90,
    ];

    protected $fillable = [
        'files',
        'paths',
        'attachable_type',
        'attachable_id',
    ];

    protected $casts = [
        'files' => 'collection',
        'paths' => 'collection',
    ];

    public static function booted(): void
    {
        static::deleting(fn (self $attachment) => $attachment->purge());
    }

    public function request(): MorphOne
    {
        return $this->morphOne(Request::class, 'attachable');
    }

    public function action(): MorphOne
    {
        return $this->morphOne(Action::class, 'attachable');
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function empty(): Attribute
    {
        return Attribute::make(function (): bool {
            return $this->paths->isEmpty();
        })->shouldCache();
    }

    public function purge(): void
    {
        $this->files->each(fn ($file) => Storage::delete($file));
    }
}
