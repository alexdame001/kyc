<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SMSService
{
    protected $apiUrl = "https://api.smartsmssolutions.com/smsapi.php";
    protected $token = "0kJrlNuCJNBZfzKG4ez56MEQSg36xmePydhNDtVQ3codzntWWr";
    protected $sender = "IBEDC";

    public function sendSMS($to, $message)
    {
        $response = Http::get($this->apiUrl, [
            'username' => $this->token,  // API key goes here
            'password' => $this->token,  // sometimes same as API key
            'sender'   => $this->sender,
            'recipient'=> $to,
            'message'  => $message,
        ]);

        return $response->body();
    }

}
