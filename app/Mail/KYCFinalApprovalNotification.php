<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class KYCFinalApprovalNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $fullName;

    public function __construct($fullName)
    {
        $this->fullName = $fullName;
    }

    public function build()
    {
        return $this->subject('KYC Approved')
                    ->view('emails.kyc-approved');
    }
}
