@extends('filament.panels.auth.layout.base')

@section('content')
    @if ($this->unauthorized)
        <div class="relative flex items-center">
            <div class="flex-grow border-t border-gray-400"></div>
            <span class="flex-shrink mx-4 text-gray-400">or</span>
            <div class="flex-grow border-t border-gray-400"></div>
        </div>

        <p class="text-sm text-center text-gray-500 dark:text-gray-400">
            You are not authorized to view this invitation.
        </p>

        {{ $this->homeAction }}
    @elseif ($this->valid)
        <div class="flex flex-col items-center text-center">
            <div class="flex flex-col items-center pb-3">
                <img class="rounded-full size-11" src="{{ $this->invitee->avatar_url }}" alt="User Avatar">

                <div class="font-medium text-center dark:text-white">
                    {{ $this->invitee->name }}
                </div>

                <div class="text-lg font-semibold text-gray-900 dark:text-white">
                    You have been invited to join&hellip;
                </div>
            </div>

            <div class="flex items-center gap-4 px-6 py-4 bg-gray-100 rounded-lg shadow-sm min-w-72 dark:bg-gray-800">
                <img class="rounded-full shadow size-24" src="{{ $this->organization->logo_url }}" alt="Organization Logo">

                <div class="text-left">
                    <div class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ $this->organization->code }}
                    </div>

                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ $this->organization->name }}
                        </span>
                    </div>

                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Role:
                        <span class="text-sm font-semibold text-primary dark:text-primary-light">
                            {{ $this->role->getLabel() }}
                        </span>
                    </div>
                </div>
            </div>

            @if($this->inviter)
                <div class="flex flex-col items-center p-4 space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <div class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    Invitation sent by
                                </span>
                                {{ $this->inviter->name }}
                            </div>

                            <div class="text-xs text-gray-700 dark:text-gray-300">
                                {{ $this->time }}
                            </div>
                        </div>

                        <img class="rounded-full size-8" src="{{ $this->inviter->avatar_url }}" alt="Inviter Avatar">
                    </div>
                </div>
            @endif
        </div>

        {{ $this->acceptAction }}
    @else
        {{ $this->homeAction }}
    @endif

    {{ $this->logoutAction }}
@endsection
