<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ValidationController extends Controller
{

    // RKAM and BM Dashboard Logic Implementation

// In a dedicated controller (e.g., ValidationController.php)

public function showRKAMDashboard()
{
    $mdPostpaid = DB::connection('sqlsrv89')->table('customerNew')
        ->whereIn('TariffID', [6,11,13,19,10,5,14,17])
        ->pluck('AccountNo')
        ->toArray();

    $mdPrepaid = DB::connection('sqlsrv81')->table('Customers')
        ->whereIn('TariffID', [5,6,10,11,13,14,16,17,19])
        ->pluck('MeterNo')
        ->toArray();

    $mdAccountIds = array_merge($mdPostpaid, $mdPrepaid);

    $pendingForms = DB::table('kyc_forms')
        ->whereIn('account_id', $mdAccountIds)
        ->where('rkam_status', 'pending')
        ->get();

    return view('rkam.dashboard', compact('pendingForms'));
}

public function showBMDashboard()
{
    $nmdPostpaid = DB::connection('sqlsrv89')->table('customerNew')
        ->whereIn('TariffID', [1,4,7,12,9])
        ->pluck('AccountNo')
        ->toArray();

    $nmdPrepaid = DB::connection('sqlsrv81')->table('Customers')
        ->whereIn('TariffID', [1,2,4,7,8,9,12])
        ->pluck('MeterNo')
        ->toArray();

    $nmdAccountIds = array_merge($nmdPostpaid, $nmdPrepaid);

    $pendingForms = DB::table('kyc_forms')
        ->whereIn('account_id', $nmdAccountIds)
        ->where('bm_status', 'pending')
        ->get();

    return view('bm.dashboard', compact('pendingForms'));
}

}
