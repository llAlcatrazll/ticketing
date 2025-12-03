
<x-filament-panels::form id="form" wire:submit="submit" class="lg:px-96 lg:py-12">
    <div class="bg-gray-100 py-8 rounded-lg border-slate-900 border text-center text-3xl font-bold lg:mb-4">
        Feedback Form
    </div>
    {{ $this->form }}
    <x-filament-panels::form.actions
        :actions="$this->getCachedFormActions()"
        :full-width="$this->hasFullWidthFormActions()"
    />
</x-filament-panels::form>

