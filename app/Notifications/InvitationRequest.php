<?php

namespace App\Notifications;

use App\Enums\UserRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class InvitationRequest extends Notification
{
    use Queueable;

    public readonly string $url;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected User $recipient,
        protected User $referrer,
        protected Organization $organization,
        protected UserRole $role,
        protected Carbon $time,
    ) {
        $this->url = URL::temporarySignedRoute('filament.auth.auth.invitation-request.prompt', $time->clone()->addDay(), [
            'to' => $organization->id,
            'as' => $role,
            'at' => $time->timestamp,
            'referrer' => $referrer->email,
            'recipient' => $recipient->email,
        ]);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $referrer = $this->referrer->name;

        $organization = $this->organization->name;

        $role = $this->role->value;

        return (new MailMessage)
            ->subject('Invitation Request Notification')
            ->line("You have received an invitation from {$referrer} to join the organization {$organization} as one of their {$role}s.")
            ->action('View invitation', $this->url)
            ->line('This invitation request link will expire in 24 hours.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
