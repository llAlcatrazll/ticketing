<?php

namespace App\Models;

use App\Enums\AssignmentResponse;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Assignee extends Pivot
{
    use HasUlids;

    protected $table = 'assignees';

    protected $casts = [
        'response' => AssignmentResponse::class,
        'responded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->assigned();
    }

    public function request(): BelongsToMany
    {
        return $this->belongsToMany(Request::class);
    }

    public function assigned(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigner_id');
    }
}
