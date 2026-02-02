<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class IBEDCKYCMAIL extends Mailable
{
    use Queueable, SerializesModels;

    public $firstName;

    public function __construct($firstName)
    {
        $this->firstName = $firstName;
    }

    public function build()
    {
        return $this->subject('Your KYC Profile Activation')
                    ->markdown('emails.kyc')
                    ->with(['firstName' => $this->firstName]);
    }
}
