<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\DB;

class AuditLogger
{
    public static function log($action, $kycFormId = null, $description = null)
    {
        if (Auth::check()) {
            DB::table('audit_logs')->insert([
                'users_id'     => Auth::id(),
                'action'      => $action,
                'kyc_form_id' => $kycFormId,
                'description' => $description,
                'ip_address'  => Request::ip(),
                'created_at'  => now(),
            ]);
        }
    }
}
