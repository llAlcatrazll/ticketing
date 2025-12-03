<?php

namespace App\Models;

use App\Enums\ActionResolution;
use App\Enums\ActionStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;

class Action extends Model
{
    use HasUlids;

    protected $fillable = [
        'request_id',
        'user_id',
        'remarks',
        'status',
        'resolution',
    ];

    protected $casts = [
        'status' => ActionStatus::class,
        'resolution' => ActionResolution::class,
    ];

    public static function booted(): void
    {
        static::deleting(function (self $action) {
            $action->attachment?->delete();

            Storage::deleteDirectory("attachments/{$action->request_id}");
        });
    }

    public function attachment(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'attachable');
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function remarks(): Attribute
    {
        return Attribute::make(
            set: fn (?string $remarks): ?string => empty($remarks) ? null : preg_replace('/[^\S\r\n]+$/m', '', $remarks ?? ''),
            get: function (?string $remarks): ?string {
                return match ($this->status) {
                    ActionStatus::ASSIGNED => $this->formatAssigned(),
                    ActionStatus::TAGGED => $this->formatTags(),
                    ActionStatus::RECATEGORIZED => $this->formatRecategorized(),
                    default => empty($remarks) ? null : preg_replace('/(?<!  )$/m', '  ', $remarks),
                };
            },
        )->shouldCache();
    }

    private function formatAssigned(): ?string
    {
        if ($this->status !== ActionStatus::ASSIGNED) {
            return null;
        }

        $remarks = $this->getRawOriginal('remarks');

        $pattern = '/\* ([a-z0-9]+)/i';

        preg_match_all($pattern, $remarks, $matches);

        $id = $matches[1] ?? [];

        if (count($id) === 1) {
            $user = User::find($id[0]);

            return preg_replace_callback($pattern, fn () => 'To: '.($user->name ?? '*(<u>anonymous</u>)*'), $remarks);
        }

        $users = User::whereIn('id', $id)
            ->pluck('name', 'id');

        $mapped = preg_replace_callback($pattern, fn ($match) => '* '.($users[$match[1]] ?? '*(<u>anonymous</u>)*'), $remarks);

        return "To:\n{$mapped}";
    }

    private function formatTags(): ?string
    {
        if ($this->status !== ActionStatus::TAGGED) {
            return null;
        }

        $remarks = $this->getRawOriginal('remarks');

        preg_match_all('/([+-])(\w{26})/', $remarks, $matches);

        $existing = Tag::whereIn('id', $matches[2])
            ->get(['id', 'name', 'color'])
            ->keyBy('id');

        $tags = collect($matches[2])->map(function ($id, $index) use ($matches, $existing) {
            $operation = $matches[1][$index];

            if ($existing->has($id)) {
                $tag = $existing[$id];
                $color = $tag->color;
                $name = $tag->name;
            } else {
                $color = 'gray';
                $name = '<span class="font-mono font-bold">(non-existent)</span>';
            }

            return [
                'operation' => $operation,
                'html' => <<<HTML
                    <x-filament::badge class="w-fit" size="sm" color="{$color}">
                        {$name}
                    </x-filament::badge>
                HTML
            ];
        })->groupBy('operation')->map(function ($tags, $operation) {
            $formatted = $tags->pluck('html')->join('');

            return match ($operation) {
                '+' => "<div class='flex gap-1'>Added{$formatted}</div>",
                '-' => "<div class='flex gap-1'>Removed{$formatted}</div>",
            };
        });

        return Blade::render('<div class="flex flex-col gap-1">'.$tags->join('').'</div>');
    }

    private function formatRecategorized(): ?string
    {
        if ($this->status !== ActionStatus::RECATEGORIZED) {
            return null;
        }

        $remarks = preg_replace_callback('/(\*{1,2})([a-z0-9]{26})/', function ($matches) {
            $ulid = $matches[2];

            if ($matches[1] === '*') {
                $category = Category::find($ulid);

                return $category ? $category->name : '(non-existent)';
            } elseif ($matches[1] === '**') {

                $subcategory = Subcategory::find($ulid);

                return $subcategory ? $subcategory->name : '(non-existent)';
            }
        }, $this->getRawOriginal('remarks'));

        return preg_replace('/(?<!  )$/m', '  ', $remarks);
    }
}
