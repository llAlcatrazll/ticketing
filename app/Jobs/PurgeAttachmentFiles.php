<?php

namespace App\Jobs;

use App\Models\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class PurgeAttachmentFiles implements ShouldQueue
{
    use Queueable;

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping('purge-attachment-files-job')];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $execute = function () {
            $purgable = Attachment::where(function (Builder $query) {
                foreach (Attachment::$purgable as $model => $duration) {
                    $query->orWhere(function ($query) use ($model, $duration) {
                        $query->where('attachable_type', $model);

                        $query->where('created_at', '<=', now()->subDays($duration));
                    });
                }
            });

            $purgable->lazyById()->each->purge();
        };

        

        $execute();
    }
}
