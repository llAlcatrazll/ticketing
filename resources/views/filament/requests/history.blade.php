@use(App\Enums\ActionStatus)
@use(App\Filament\Helpers\ColorToHex)

@php($chat ??= false)
@php($progress ??= true)
@php($descending ??= true)

@if($request->action)
    <section wire:poll class="space-y-3">
        <div class="pl-[0.75rem] space-y-3">
            <ol class="relative border-gray-200 border-s dark:border-gray-700">
                @if(!$chat && $progress && in_array($request->action->status, [ActionStatus::STARTED, ActionStatus::RESPONDED]))
                    <li class="mb-4 ms-6">
                        <span
                            class='absolute flex items-center justify-center w-6 h-6 bg-white rounded-full -start-3 ring-8 ring-white dark:ring-gray-900 dark:bg-gray-900'
                            @style(['color:'.ColorToHex::convert('gray')])
                        >
                            <x-filament::icon class="w-6 h-6" :icon="ActionStatus::IN_PROGRESS->getIcon()"/>
                        </span>

                        <div class="flex justify-between">
                            <h3 class="flex items-center mb-1 text-base italic uppercase">
                                {{ ActionStatus::IN_PROGRESS->getLabel() }}
                            </h3>
                        </div>

                        <div class="block mb-2 text-sm font-light leading-none text-neutral-500">
                            Please be patient as the assigned

                            @switch(true)
                                @case($request->assignees()->count() > 2)
                                    agents are
                                    @break
                                @default
                                    agent is
                            @endswitch

                            currently working on the request.
                        </div>
                    </li>
                @elseif(!$chat && $progress && in_array($request->action->status, [ActionStatus::SUSPENDED]))
                    <li class="mb-4 ms-6">
                        <span
                            class='absolute flex items-center justify-center w-6 h-6 bg-white rounded-full -start-3 ring-8 ring-white dark:ring-gray-900 dark:bg-gray-900'
                            @style(['color:'.ColorToHex::convert(ActionStatus::ON_HOLD->getColor())])
                        >
                            <x-filament::icon class="w-6 h-6" :icon="ActionStatus::ON_HOLD->getIcon()"/>
                        </span>

                        <div class="flex justify-between">
                            <h3 class="flex items-center mb-1 text-base italic uppercase">
                                {{ ActionStatus::ON_HOLD->getLabel() }}
                            </h3>
                        </div>

                        <div class="block mb-2 text-sm font-light leading-none text-neutral-500">
                            The request is currently on hold. The requester is required to comply the agent's
                            instructions and or requirements before further action can be taken.
                        </div>
                    </li>
                @endif

                @foreach (
                    $request->actions
                        ->when($chat, fn ($actions) => $actions->filter(fn ($action) => $action->status === ActionStatus::RESPONDED))
                        ->when($descending, fn ($actions) => $actions->sortByDesc('created_at'), fn ($actions) => $actions->sortBy('created_at'))
                    as $action
                )
                    <li class="mb-4 ms-6">
                        @include('filament.requests.action', [
                            'action' => $action,
                            'chat' => $chat,
                            're' => in_array($action->status, [ActionStatus::SUBMITTED, ActionStatus::ASSIGNED, ActionStatus::QUEUED], true) &&
                                $request->actions
                                    ->when($descending, fn ($actions) => $actions->sortBy('created_at'))
                                    ->first(fn ($act) => $act->status === $action->status)
                                    ->isNot($action)
                        ])
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
@endif
