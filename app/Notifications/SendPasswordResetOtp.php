<?php

// app/Notifications/SendPasswordResetOtp.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;  // Add this
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SendPasswordResetOtp extends Notification implements ShouldQueue  // Implement ShouldQueue
{
    use Queueable;

    protected string $otp;

    public function __construct(string $otp)
    {
        $this->otp = $otp;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your IBEDC Password Reset OTP')
            ->line("Use this code to reset your password: **{$this->otp}**")
            ->line('This code will expire in 5 minutes.');
    }
}
