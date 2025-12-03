<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Record extends Pivot
{
    use HasUlids;

    protected $table = 'records';

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }
}
