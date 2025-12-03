<?php

namespace App\Models;

use App\Enums\Feedback as EnumsFeedback;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feedback extends Model
{
    use HasUlids;
    protected $fillable = [
        'feedbacks',
        'category_id',
        'request_id',
        'organization_id',
        'user_id',
        'email',
        'client_type',
        'gender',
        'age',
        'residence',
        'expectation',
        'strength',
        'improvement',
        'service_type',
    ];

    protected function casts(): array
    {
        return [
            'feedbacks' => 'array',
            'service_type' => EnumsFeedback::class,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    protected function sqdAverage(): Attribute
    {
        return new Attribute(
            get: fn () => $this->responses
                ->filter(fn ($response) => $response->question_type === 'Service Quality Dimension')
                ->avg('answer'),
        );
    }

    public function getAnswer($question)
    {
        return $this->responses->where('question', $question)->first()->answer ?? null;
    }
}
