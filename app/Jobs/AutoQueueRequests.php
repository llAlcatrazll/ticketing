<?php

namespace App\Jobs;

use App\Enums\ActionStatus;
use App\Models\Organization;
use App\Models\Request;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class AutoQueueRequests implements ShouldQueue
{
    use Queueable;

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping('auto-queue-request-job')];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $execute = function () {
            foreach (Organization::whereNotNull('settings->auto_queue')->get() as $organization) {
                $organization->requests()
                    ->whereHas('action', function (Builder $query) use ($organization) {
                        $query->where('status', ActionStatus::SUBMITTED)
                            ->where('created_at', '<=', now()->subMinutes($organization->settings['auto_queue']));
                    })
                    ->lazyById()
                    ->each(function (Request $request) {
                        $request->actions()->create([
                            'status' => ActionStatus::QUEUED,
                            'system' => true,
                        ]);
                    });
            }
        };

        $execute();
    }
}
