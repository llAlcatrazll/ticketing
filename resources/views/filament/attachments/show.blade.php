<ul class="max-w-md space-y-1 text-sm text-gray-500 list-inside dark:text-gray-400 flex flex-col">
    @foreach (($attachment ??= $getState())?->paths ?? [] as $file => $name)
        <li class="inline-block truncate font-mono">
            @if(Storage::exists($attachment->paths->search($name)))
                <a href="{{ route('file.attachment', [$attachment->id, $name]) }}" download="{{ $name }}">
                    <x-filament::icon class="inline size-6" icon="gmdi-download-o" />
                    {{ str($name)->basename()->reverse()->limit(36)->reverse() }}
                </a>
            @else
                <span class="text-red-500">
                    <x-filament::icon class="inline size-6" icon="gmdi-delete-forever-o" />
                    {{ str($name)->basename()->reverse()->limit(36)->reverse() }}
                </span>
            @endif
        </li>
    @endforeach
</ul>
