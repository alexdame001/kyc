<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StaffAccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $staff;
    public $plainPassword;

    /**
     * Create a new message instance.
     */
    public function __construct($staff, $plainPassword)
    {
        $this->staff = $staff;
        $this->plainPassword = $plainPassword;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your IBEDC KYC Application Account')
                    ->markdown('emails.staff.created');
    }
}
