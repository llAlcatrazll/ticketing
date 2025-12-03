@php($request ??= null)

@if ($request)
    <div class="prose max-w-none dark:prose-invert [&>*:first-child]:mt-0 [&>*:last-child]:mb-0 prose-sm text-sm leading-6 text-gray-950 dark:text-white">
        {{ str($request->body)->markdown()->sanitizeHtml()->toHtmlString() }}
    </div>

    <div @class(["mt-4" => $request->attachment?->files->isNotEmpty()])>
        @includeWhen($request->attachment?->files->isNotEmpty(), 'filament.requests.attachment', ['attachment' => $request->attachment])
    </div>
@endif
