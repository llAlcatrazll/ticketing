<?php

namespace App\Filament\Actions\Concerns;

use App\Enums\ActionStatus;
use App\Models\Request;
use Illuminate\Support\Facades\Auth;

trait RestoreRequest
{
    protected function bootRestoreRequest(): void
    {
        $this->successNotificationTitle('Request restored');

        $this->action(function (Request $request): void {
            if (! method_exists($request, 'restore')) {
                $this->failure();

                return;
            }

            $result = $this->process(static function () use ($request): bool {
                return Auth::user()->root
                    ? $request->restore()
                    : $request->restore() && $request->actions()->create([
                        'status' => ActionStatus::RESTORED,
                        'user_id' => Auth::id(),
                    ]);
            });

            if (! $result) {
                $this->failure();

                return;
            }

            $this->success();
        });
    }
}
