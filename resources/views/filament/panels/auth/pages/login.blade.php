@extends('filament.panels.auth.layout.base')

@section('content')
{{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

<x-filament-panels::form id="form" wire:submit="authenticate">
    {{ $this->form }}

    <x-filament-panels::form.actions
        :actions="$this->getCachedFormActions()"
        :full-width="$this->hasFullWidthFormActions()"
    />
</x-filament-panels::form>

{{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
@endsection

@section ('subheading')
    {{ __('filament-panels::pages/auth/login.actions.register.before') }}

    {{ $this->registerAction }}
@endsection
