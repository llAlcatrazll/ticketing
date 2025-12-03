@extends('filament.panels.auth.layout.base')

@section('content')
<x-filament-panels::form id="form" wire:submit="initialize">
    {{ $this->form }}

    <x-filament-panels::form.actions
        :actions="$this->getCachedFormActions()"
        :full-width="$this->hasFullWidthFormActions()"
    />
</x-filament-panels::form>

{{ $this->logoutAction }}
@endsection
