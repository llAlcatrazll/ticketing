<?php

namespace App\Models;

use App\Enums\RequestStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Action extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'request_id',
        'user_id',
        'status',
        'response',
        'responded_at',
        'remarks',
        'time',
    ];

    protected $casts = [
        'status' => RequestStatus::class,
    ];

    public function request(): BelongsToMany
    {
        return $this->belongsToMany(Request::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachment(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'attachable');
    }
}
