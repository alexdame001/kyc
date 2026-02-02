<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Agency;

class AgencyEmailVerification extends Notification
{
    protected $agency;

    public function __construct(Agency $agency)
    {
        $this->agency = $agency;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $verificationUrl = url("/api/agencies/verify/{$this->agency->verification_token}");

        return (new MailMessage)
                    ->subject('Verify Your Agency Email')
                    ->greeting('Hello ' . $this->agency->name)
                    ->line('Please verify your email address to complete agency registration.')
                    ->action('Verify Email', $verificationUrl)
                    ->line('This link will expire in 24 hours.')
                    ->line('If you did not request this, please ignore this email.');
    }
}
