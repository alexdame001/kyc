<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\KycForm;

class DashboardService
{
    public function getStageCounts()
    {
        $counts = [
            'rkam'    => ['approved' => 0, 'pending' => 0, 'rejected' => 0],
            'bm'      => ['approved' => 0, 'pending' => 0, 'rejected' => 0],
            'rico'    => ['approved' => 0, 'pending' => 0, 'rejected' => 0],
            'billing' => ['approved' => 0, 'pending' => 0, 'rejected' => 0],
        ];

        // RKAM (from SP)
        $rkam = DB::connection('sqlsrv')->select("EXEC sp_get_rkam_dashboard");
        $counts['rkam']['pending']  = count($rkam);
        $counts['rkam']['approved'] = KycForm::where('rkam_status', 'approved')->count();
        $counts['rkam']['rejected'] = KycForm::where('rkam_status', 'rejected')->count();

        // BM (from SP)
        $bm = DB::connection('sqlsrv')->select("EXEC sp_get_bm_dashboard");
        $counts['bm']['pending']  = count($bm);
        $counts['bm']['approved'] = KycForm::where('bm_status', 'approved')->count();
        $counts['bm']['rejected'] = KycForm::where('bm_status', 'rejected')->count();

        // RICO (only if RKAM & BM approved)
        $counts['rico']['pending'] = KycForm::where('rkam_status', 'approved')
                                            ->where('bm_status', 'approved')
                                            ->where('rico_status', 'pending')->count();
        $counts['rico']['approved'] = KycForm::where('rico_status', 'approved')->count();
        $counts['rico']['rejected'] = KycForm::where('rico_status', 'rejected')->count();

        // Billing (only if RICO approved)
        $counts['billing']['pending'] = KycForm::where('rico_status', 'approved')
                                               ->where('billing_status', 'pending')->count();
        $counts['billing']['approved'] = KycForm::where('billing_status', 'approved')->count();
        $counts['billing']['rejected'] = KycForm::where('billing_status', 'rejected')->count();

        return $counts;
    }

    public function getTotals()
    {
        return [
            'approved' => KycForm::where('rkam_status', 'approved')
                                 ->where('bm_status', 'approved')
                                 ->where('rico_status', 'approved')
                                 ->where('billing_status', 'approved')->count(),

            'pending'  => KycForm::where(function ($q) {
                                $q->where('rkam_status', 'pending')
                                  ->orWhere('bm_status', 'pending')
                                  ->orWhere('rico_status', 'pending')
                                  ->orWhere('billing_status', 'pending');
                            })->count(),

            'rejected' => KycForm::where(function ($q) {
                                $q->where('rkam_status', 'rejected')
                                  ->orWhere('bm_status', 'rejected')
                                  ->orWhere('rico_status', 'rejected')
                                  ->orWhere('billing_status', 'rejected');
                            })->count(),
        ];
    }
}
