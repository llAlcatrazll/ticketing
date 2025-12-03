<?php

namespace App\Filament\Actions\Concerns\Notifications;

use App\Enums\ActionResolution;
use App\Enums\ActionStatus;
use App\Models\Request;
use App\Models\User;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\Authenticatable;
use ReflectionClass;

/**
 * @property-read ActionStatus $requestAction
 * @property-read ActionResolution $requestResolution
 */
trait CanNotifyUsers
{
    use CanCustomizeProcess;

    protected function notifyUsers(?Request $request = null)
    {
        $reflection = new ReflectionClass($this);

        if (! ($reflection->hasProperty('requestAction') && $reflection->getProperty('requestAction')->isStatic()) || static::$requestAction === null) {
            return;
        }

        $notify = function (User $user, string $heading, ?string $description, ?string $icon, ?string $color) {
            Notification::make()
                ->title($heading)
                ->body($description)
                ->color($color)
                ->icon($icon)
                ->sendToDatabase($user, true);
        };

        $this->process(function (?Request $record, Authenticatable $authenticated, array $data = []) use ($notify, $request) {
            $request ??= $record;

            $heading = match (static::$requestAction) {
                ActionStatus::SUBMITTED => "New {$request->class?->value} received",
                ActionStatus::ASSIGNED => "New {$request->class->value} request assignment",
                ActionStatus::STARTED => "{$request->class->getLabel()} request #{$request->code} started",
                ActionStatus::SUSPENDED => "{$request->class->getLabel()} request #{$request->code} is on hold",
                ActionStatus::COMPLIED => "{$request->class->getLabel()} request #{$request->code} complied",
                ActionStatus::COMPLETED => "{$request->class->getLabel()} request #{$request->code} completed",
                ActionStatus::REJECTED => "{$request->class->getLabel()} request assignment rejected",
                ActionStatus::RESPONDED => "$authenticated->name has responded to ".($authenticated->id !== $request->user_id ? 'your' : 'their')." inquiry #{$request->code}.",
                ActionStatus::CLOSED => "Request #{$request->code} has been closed",
                default => null,
            };

            if ($heading === null) {
                return;
            }

            $description = match (static::$requestAction) {
                ActionStatus::SUBMITTED => "A new {$request->class?->value} #{$request?->code} has been submitted by {$authenticated->name}.",
                ActionStatus::ASSIGNED => "{$request->class->getLabel()} #{$request->code} has been assigned to you by {$authenticated->name}.",
                ActionStatus::STARTED => "{$authenticated->name} is now processing your {$request->class->value} request.",
                ActionStatus::SUSPENDED => "Please comply with the agent's instructions and or requirements to resume processing your {$request->class->value} request.",
                ActionStatus::COMPLIED => "{$authenticated->name} has complied with your {$request->class->value} request.",
                ActionStatus::COMPLETED => "The request has been completed successfully by {$authenticated->name}.",
                ActionStatus::REJECTED => "The assignment has been rejected by {$authenticated->name} for {$request->class->value} request #{$request->code}.",
                ActionStatus::CLOSED => match (static::$requestResolution ?? ActionResolution::tryFrom($data['resolution'])) {
                    ActionResolution::RESOLVED => "The request has been successfully resolved by {$authenticated->name}.",
                    ActionResolution::UNRESOLVED => "The request has been closed by {$authenticated->name} without resolution provided.",
                    ActionResolution::INVALIDATED => "The request has been found invalid by {$authenticated->name}.",
                    ActionResolution::ACKNOWLEDGED => "The request has been acknowledged by {$authenticated->name}.",
                    ActionResolution::CANCELLED => "The request has been canceled by {$authenticated->name}.",
                    default => null,
                },
                default => null,
            };

            $icon = match (static::$requestAction) {
                ActionStatus::SUBMITTED => 'gmdi-move-to-inbox-o',
                ActionStatus::STARTED => ActionStatus::IN_PROGRESS->getIcon(),
                ActionStatus::SUSPENDED => ActionStatus::ON_HOLD->getIcon(),
                ActionStatus::CLOSED => (static::$requestResolution ?? ActionResolution::tryFrom($data['resolution']))->getIcon(),
                default => static::$requestAction->getIcon(),
            };

            $color = match (static::$requestAction) {
                ActionStatus::CLOSED => (static::$requestResolution ?? ActionResolution::tryFrom($data['resolution']))->getColor(),
                default => static::$requestAction->getColor(),
            };

            $users = match (static::$requestAction) {
                ActionStatus::ASSIGNED => User::find($data['assignees']),
                ActionStatus::SUBMITTED,
                ActionStatus::REJECTED => User::where('organization_id', $request->organization_id)->moderator(admin: true)->get(),
                default => $request->user->is($authenticated) ?
                    $request->assignees :
                    User::find([$request->user_id]),
            };

            $users->each(fn ($user) => $notify($user, $heading, $description, $icon, $color));
        });
    }
}
