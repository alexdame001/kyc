<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;  // Add this line

class BulkMailController extends Controller  // Now this extends the proper base class
{
    public function sendBulk(): string
    {
        $emails = [
            'al-ameen.ameen@ibedc.com',
            'al.ameenmemmcol@gmail.com',
            // add as many as you want
        ];

        $subject = "KYC Verification Update";
        $message = "Dear Customer, please log in to update your KYC information. Thank you.";

        foreach ($emails as $email) {
            Mail::raw($message, function ($mail) use ($email, $subject) {
                $mail->to($email)
                     ->subject($subject)
                     ->from('kyc@ibedc.com', 'IBEDC KYC');
            });
        }

        return "Bulk emails sent successfully!";
    }
}