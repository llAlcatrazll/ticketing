<?php

namespace App\Jobs;

use App\Enums\ActionResolution;
use App\Enums\ActionStatus;
use App\Models\Organization;
use App\Models\Request;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class AutoResolveCompletedRequests implements ShouldQueue
{
    use Queueable;

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping('auto-resolve-completed-request-job')];
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $execute = function () {
            foreach (Organization::lazyById() as $organization) {
                $organization->requests()
                    ->whereHas('action', function (Builder $query) use ($organization) {
                        $query->where('status', ActionStatus::COMPLETED)
                            ->where('created_at', '<=', now()->subHours($organization->settings['auto_resolve'] ?? config('app.requests.auto_resolve')));
                    })
                    ->lazyById()
                    ->each(function (Request $request) {
                        $request->actions()->create([
                            'status' => ActionStatus::CLOSED,
                            'resolution' => ActionResolution::RESOLVED,
                            'system' => true,
                        ]);

                        Notification::make()
                            ->title('Request #'.$request->code.' has been closed')
                            ->body('Request #'.$request->code.' has been automatically resolved by the system since no further action was taken by the requester.')
                            ->icon(ActionResolution::RESOLVED->getIcon())
                            ->sendToDatabase($request->user, true);
                    });
            }
        };

        $execute();
    }
}
