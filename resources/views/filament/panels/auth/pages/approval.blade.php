@extends('filament.panels.auth.layout.base')

@section('content')
<p class="text-sm text-center text-gray-500 dark:text-gray-400">
    Your registration is undergoing the review process, and once completed,
    you will receive an email confirming that your account has been approved and activated.
</p>

{{ $this->logoutAction }}
@endsection
