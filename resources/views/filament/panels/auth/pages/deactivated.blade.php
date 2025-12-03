@extends('filament.panels.auth.layout.base')

@section('content')
<p class="text-sm text-center text-gray-500 dark:text-gray-400">
    Your account has been deactivated by an administrator at
</p>

<p class="font-mono text-sm text-center text-gray-500 dark:text-gray-400">
    {{ $user->deactivated_at->toDateTimeString() }}
</p>

{{ $this->logoutAction }}
@endsection
