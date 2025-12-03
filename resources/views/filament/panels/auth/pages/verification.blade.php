@extends('filament.panels.auth.layout.base')

@section('content')
<p class="text-sm text-center text-gray-500 dark:text-gray-400">
    {{
        __('filament-panels::pages/auth/email-verification/email-verification-prompt.messages.notification_sent', [
            'email' => filament()->auth()->user()->getEmailForVerification(),
        ])
    }}
</p>

<p class="text-sm text-center text-gray-500 dark:text-gray-400">
    {{ __('filament-panels::pages/auth/email-verification/email-verification-prompt.messages.notification_not_received') }}

    {{ $this->resendNotificationAction }}
</p>

{{ $this->logoutAction }}
@endsection
