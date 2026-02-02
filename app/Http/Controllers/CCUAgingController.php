<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
// use DB;
// use Maatwebsite\Excel\Facades\Excel; // ✅ this line is required
use App\Exports\KycAgingExport;

class CCUAgingController extends Controller
{
    public function export()
    {
        $fileName = 'kyc-aging-' . date('Y-m-d_H-i-s') . '.csv';

        return new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');

            // CSV header row
            fputcsv($handle, [
                'ID', 'Account ID', 'Account Type', 'Full Name',
                'Updated At', 'Created At', 'Total Age (Days)',
                'Age Since Last Update', 'RICO Status', 'Billing Status',
                'Audit Status', 'Admin Status', 'Current Stage'
            ]);

            // Fetch data from your stored procedure
            $data = DB::select('EXEC sp_GetKycAgingAnalysis');

            foreach ($data as $row) {
                fputcsv($handle, [
                    $row->id ?? '',
                    $row->account_id ?? '',
                    $row->account_type ?? '',
                    $row->fullname ?? '',
                    $row->updated_at ?? '',
                    $row->created_at ?? '',
                    $row->total_age_days ?? '',
                    $row->age_since_last_update ?? '',
                    $row->rico_status ?? '',
                    $row->billing_status ?? '',
                    $row->audit_status ?? '',
                    $row->admin_status ?? '',
                    $row->current_stage ?? '',
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    // public function index()
    // {
    //     $agingData = DB::select('EXEC sp_GetKycAgingAnalysis');
    //     return view('ccu.aging-analysis', compact('agingData'));
    // }
    public function index()
    {
        // Fetch data from stored procedure
        $agingData = DB::select('EXEC sp_GetKycAgingAnalysis');

        return view('ccu.aging-analysis', compact('agingData'));
    }

   public function downloadcsv()
    {
       $fileName = 'kyc_aging_analysis_' . date('Y-m-d_H-i-s') . '.csv';
        $agingData = DB::select('EXEC sp_GetKycAgingAnalysis');

        $response = new StreamedResponse(function () use ($agingData) {
            $handle = fopen('php://output', 'w');

            // CSV headers
            fputcsv($handle, [
                'ID', 'Account ID', 'Account Type', 'Full Name',
                'Created At', 'Updated At', 'Total Age (Days)',
                'Age Since Last Update', 'RICO Status', 'Billing Status',
                'Audit Status', 'Admin Status', 'Current Stage'
            ]);

            // CSV rows
            foreach ($agingData as $row) {
                fputcsv($handle, [
                    $row->id,
                    $row->account_id,
                    $row->account_type,
                    $row->fullname,
                    $row->created_at,
                    $row->updated_at,
                    $row->total_age_days,
                    $row->age_since_last_update,
                    $row->rico_status,
                    $row->billing_status,
                    $row->audit_status,
                    $row->admin_status,
                    $row->current_stage
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }
}
