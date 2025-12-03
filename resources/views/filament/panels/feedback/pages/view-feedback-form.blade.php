<x-filament::page>
    <div class="flex flex-col items-center">
        <div class="text-center bg-red-50 border border-red-400 text-red-700 px-3 py-2 rounded relative mb-4 w-96" role="alert">
            <strong class="font-bold">Warning!</strong>
            <span class="block sm:inline">This is a read-only view of the feedback form.</span>
        </div>
         @if($isLoading ?? false)
            <div class="flex items-center justify-center h-96">
                <div class="text-center">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-4"></div>
                    <p class="text-gray-600">Loading feedback data...</p>
                </div>
            </div>
        @else
            <div class="relative" x-data="{ iframeLoading: true }">
                <div x-show="iframeLoading" class="absolute inset-0 flex items-center justify-center bg-gray-100 border border-gray-300 rounded z-10" style="width: 1080px; height: 800px;">
                    <div class="text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mb-4"></div>
                        <p class="text-gray-600">Loading feedback form...</p>
                    </div>
                </div>

                <iframe
                    src="{{ route('feedback.form.print', $record->id) }}"
                    width="1080px"
                    height="800px"
                    frameborder="0"
                    class="border border-gray-300 rounded"
                    title="Feedback Form"
                    @load="iframeLoading = false">
                </iframe>
            </div>
        @endif
    </div>

</x-filament::page>
