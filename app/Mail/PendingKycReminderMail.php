<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PendingKycReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $staffName;
    public $pendingCount;
    public $businessUnits;
    public $dashboardUrl;

   public function __construct($staffName, $pendingCount, $businessUnits)
{
    $this->staffName     = $staffName;
    $this->pendingCount  = $pendingCount;
    $this->businessUnits = $businessUnits;
    $this->dashboardUrl  = 'https://kyc.ibedc.com/bm/dashboard';  // Production URL
}

    public function build()
    {
        return $this->from('kyc@ibedc.com', 'IBEDC KYC')
                    ->subject('Pending KYC Updates Awaiting Your Review')
                    ->markdown('emails.pending-kyc-reminder');
    }
}