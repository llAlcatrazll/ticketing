@use(Illuminate\Support\Facades\Storage)

<ol class="flex flex-col space-y-3 text-sm">
    @foreach (($attachment ??= $getState())?->paths ?? [] as $file => $name)
        <li class="list-item p-3 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
            @if(Storage::exists($attachment->paths->search($name)))

                @if(previewable_mime_type($mimeType = Storage::mimeType($file)))
                    <div class="mb-3 flex justify-center">
                        @if(str($mimeType)->startsWith('image/'))
                            <img
                                class="max-w-full max-h-96 rounded-lg border border-gray-300 dark:border-gray-600 object-contain"
                                src="{{ route('file.attachment', [$attachment->id, $name]) }}"
                                alt="{{ $name }}"
                            />
                        @else
                            <embed
                                class="w-full h-96 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900"
                                src="{{ route('file.attachment', [$attachment->id, $name]) }}"
                                type="{{ $mimeType }}"
                            />
                        @endif
                    </div>
                @endif

                <a href="{{ route('file.attachment', [$attachment->id, $name]) }}" download="{{ $name }}" class="flex items-center gap-2 text-gray-700 dark:text-gray-300 hover:text-primary-600 dark:hover:text-primary-400 transition-colors font-mono">
                    <x-filament::icon class="size-5 flex-shrink-0" icon="gmdi-download-o" />
                    <span class="truncate">{{ str($name)->basename()->reverse()->limit(50)->reverse() }}</span>
                </a>
            @else
                <span class="flex items-center gap-2 text-red-500 dark:text-red-400 font-mono">
                    <x-filament::icon class="size-5 flex-shrink-0" icon="gmdi-delete-forever-o" />
                    <span class="truncate">{{ str($name)->basename()->reverse()->limit(50)->reverse() }}</span>
                </span>
            @endif
        </li>
    @endforeach
</ol>
