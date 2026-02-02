<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendKycRejectionSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $kyc;
    public $phone;

    public function __construct($kyc, $phone)
    {
        $this->kyc = $kyc;
        $this->phone = $this->formatNigerianPhone($phone); // e.g., '08012345678'
    }

    private function formatNigerianPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone); // Strip non-digits
        if (strlen($phone) == 11 && substr($phone, 0, 1) === '0') {
            return $phone; // Already local (e.g., 08012345678)
        }
        if (strlen($phone) == 10 && (substr($phone, 0, 1) === '8' || substr($phone, 0, 1) === '9' || substr($phone, 0, 1) === '7' || substr($phone, 0, 1) === '6' || substr($phone, 0, 1) === '5' || substr($phone, 0, 1) === '4' || substr($phone, 0, 1) === '3' || substr($phone, 0, 1) === '2' || substr($phone, 0, 1) === '1')) {
            return '0' . $phone; // Add leading 0 if 10 digits (rare)
        }
        if (strlen($phone) == 13 && substr($phone, 0, 3) === '234') {
            return '0' . substr($phone, 3); // Convert 234xxxxxxxxx to 0xxxxxxxxx
        }
        // Fallback/log invalid
        Log::warning("Invalid NG phone format: {$phone}");
        return null;
    }

    public function handle()
    {
        if (!$this->phone) return; // Skip if unformatable

        $message = "IBEDC KYC Alert: Your update for {$this->kyc->account_id} needs revision. Reason: " . substr($this->kyc->reject_remarks, 0, 140) . ". Log in at yourapp.com/customer-login to resubmit. Reply STOP to opt-out.";
        // Truncate to 160 chars max; shorten URL as needed.

        $response = Http::asForm()->timeout(30)->post(config('services.smartsmssolutions.base_url'), [
            'token' => config('services.smartsmssolutions.token'),
            'sender' => config('services.smartsmssolutions.sender_id'),
            'to' => $this->phone, // Single recipient; comma-separate for bulk
            'message' => $message,
        ]);

        $body = $response->json();
        if ($response->successful() && isset($body['state']) && $body['state'] === 'success') {
            Log::info("SMS sent to {$this->phone} for KYC {$this->kyc->id}: {$body['Message']}");
        } else {
            Log::warning("SMS failed for {$this->phone} (KYC {$this->kyc->id}): " . json_encode($body));
            $this->release(300); // Requeue in 5 min, up to 3 tries
        }
    }
}