@use(App\Enums\ActionStatus)
@use(App\Enums\ActionResolution)
@use(App\Filament\Helpers\ColorToHex)

@php($chat ??= false)
@php($re ??= false)

<div>
    @if (isset($action))
        <span
            class='absolute flex items-center justify-center w-6 h-6 bg-white rounded-full -start-3 ring-8 ring-white dark:ring-gray-900 dark:bg-gray-900'
            @style(['color:'.ColorToHex::convert($action->status === ActionStatus::CLOSED ? $action->resolution->getColor() : $action->status->getColor())])
        >
            <x-filament::icon class="w-6 h-6"
                              icon="{{ $action->status === ActionStatus::CLOSED ? $action->resolution->getIcon() : $action->status->getIcon() }}"/>
        </span>

        <div class="flex justify-between">
            <h3 @class(["flex items-center mb-1 text-base", "font-mono tracking-tighter" => is_null($action->user)])>
                @if ($action->user)
                    {{ $action->user->name }}

                    @if ($chat)
                        {{ $action->user->id === Auth::id() ? '(You)' : '' }}
                    @endif
                @elseif($action->system)
                    <span class="italic font-semibold">
                        System
                    </span>
                @else
                    (non-existent user)
                @endif
            </h3>

            <time class="text-sm font-light leading-none text-neutral-500">
                {{ $action->created_at->diffForHumans() }}
            </time>
        </div>

        <time class="block mb-2 text-sm font-light leading-none text-neutral-500">
            @if (!$chat)
                <span @class(["font-bold"])>
                    {{ $re ? "Re{$action->status->value}" : $action->status->getLabel() }}

                    @if($action->status === ActionStatus::CLOSED)
                        ({{ $action->resolution->getLabel() }})
                    @endif
                </span>
                on
            @endif

            {{ $action->created_at->format('jS \of F Y \a\t H:i') }}
        </time>

        @if ($action->remarks)
            @if ($chat || $action->system || in_array($action->status, [ActionStatus::TAGGED, ActionStatus::ASSIGNED, ActionStatus::RECATEGORIZED, ActionStatus::RECLASSIFIED]))
                <div
                    class="prose max-w-none dark:prose-invert [&>*:first-child]:mt-0 [&>*:last-child]:mb-0 prose-sm text-sm leading-6 text-gray-950 dark:text-white">
                    {{ str($action->remarks)->when($action->status !== ActionStatus::TAGGED, fn ($remarks) => $remarks->markdown()->sanitizeHtml())->toHtmlString() }}
                </div>
            @else
                <div class="p-3 text-base bg-gray-100 rounded-md dark:bg-gray-800">
                    <span class="text-sm text-neutral-500">
                        <x-filament::icon class="inline size-6" icon="gmdi-format-quote-o"/>
                    </span>

                    <div
                        class="prose max-w-none dark:prose-invert [&>*:first-child]:mt-0 [&>*:last-child]:mb-0 prose-sm text-sm leading-6 text-gray-950 dark:text-white">
                        {{ str($action->remarks)->when($action->status !== ActionStatus::TAGGED, fn ($remarks) => $remarks->markdown()->sanitizeHtml())->toHtmlString() }}
                    </div>
                </div>
            @endif
        @endif

        @if ($action->attachment?->paths->isNotEmpty())
            @if ($chat)
                <div class="mt-4">
                    @include('filament.attachments.show', ['attachment' => $action->attachment])
                </div>
            @else
                <div class="p-3 mt-4 space-y-2 overflow-hidden text-base bg-gray-100 rounded-md dark:bg-gray-800">
                    <span class="text-sm text-neutral-500">
                        <x-filament::icon class="inline size-6" icon="gmdi-attachment-o"/>
                    </span>
                    @include('filament.attachments.show', ['attachment' => $action->attachment])
                </div>
            @endif
        @endif
    @endif
</div>
