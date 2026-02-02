<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;

class TestMailController extends Controller
{

public function sendTestEmail()
{
    $emailData = ['content' => 'Your email content here'];

    Mail::send([], [], function ($message) use ($emailData) {
        $message->to('al-ameen.ameen@ibedc.com')
                ->subject('Test Email')
                ->setBody($emailData['content'], 'text/plain');
    });
}
}
