<?php

namespace App\Services;
use App\Models\AuditLog;
use illuminate\support\Facades\Log;

class SmartSMSService
{
    private $sms;

    public function __construct()
    {
        require_once app_path('Services/smartsmssolutions.php'); // Path to downloaded file
        $this->sms = new \smartSMSSolutions('IBEDC', '0kJrlNuCJNBZfzKG4ez56MEQSg36xmePydhNDtVQ3codzntWWr');
    }

    public function send($recipients, $message)
    {
        try {
            $response = $this->sms->sendMSG('IBEDC', $recipients, $message); // Comma-separated phones
            $data = json_decode($response, true);

            if ($data['state'] === 'success') {
                log::info('SmartSMS sent: ' . $data['Message']);
                return ['success' => true, 'data' => $data];
            } else {
                Log::error('SmartSMS error: ' . $data['Message'] . ' (Code: ' . $data['code'] . ')');
                return ['success' => false, 'error' => $data['Message']];
            }
        } catch (\Exception $e) {
            Log::error('SmartSMS exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Bonus: Check balance
    public function getBalance()
    {
        $response = $this->sms->queryBalance();
        $data = json_decode($response, true);
        return $data['state'] === 'success' ? $data['balance'] : null;
    }
}