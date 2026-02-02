<?php

namespace App\Http\Controllers;

use App\Models\KycForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class CCUController3 extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Aging Analysis view
     */
    public function getStageData($stage)
    {
        $stage = strtoupper($stage);

        $forms = match($stage) {
            'RKAM' => DB::table('kyc_forms')->where('rkam_status','pending')->get(),
            'BM' => DB::table('kyc_forms')->where('bm_status','pending')->get(),
            'RICO' => DB::table('kyc_forms')->where('rkam_status','approved')->orWhere('bm_status','approved')->get(),
            'BILLING' => DB::table('kyc_forms')->where('rico_status','approved')->get(),
            'APPLICATIONS' => DB::table('kyc_forms')->get(),
            'COMPLETED' => DB::table('kyc_forms')->where('billing_status','approved')->get(),
            'REJECTED' => DB::table('kyc_forms')
                            ->where('rkam_status','rejected')
                            ->where('bm_status','rejected')
                            ->where('rico_status','rejected')
                            ->where('billing_status','rejected')->get(),
            'PENDING' => DB::table('kyc_forms')->where('billing_status','pending')->get(),
            default => collect()
        };

        return response()->json($forms);
    }

    public function agingAnalysis()
    {
        $agingData = DB::select('EXEC sp_GetKycAgingAnalysis');

        return view('ccu.aging', [
            'agingData' => $agingData
        ]);
    }

    public function downloadAgingCSV()
    {
        $fileName = 'kyc_aging_analysis_' . date('Y-m-d_H-i-s') . '.csv';
        $agingData = DB::select('EXEC sp_GetKycAgingAnalysis');

        $response = new StreamedResponse(function () use ($agingData) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID','Account ID','Account Type','Full Name','Created At','Updated At',
                'Total Age (Days)','Age Since Last Update','RICO Status','RKAM Status',
                'BM Status','Billing Status','Audit Status','Admin Status','Current Stage'
            ]);

            foreach ($agingData as $row) {
                fputcsv($handle, [
                    $row->id,$row->account_id,$row->account_type,$row->fullname,
                    $row->created_at,$row->updated_at,$row->total_age_days,
                    $row->age_since_last_update,$row->rico_status,$row->rkam_status,
                    $row->bm_status,$row->billing_status,$row->audit_status,
                    $row->admin_status,$row->current_stage
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename={$fileName}");
        return $response;
    }

    /**
     * KYC Show single record
     */
    public function show($id)
    {
        $form = KycForm::find($id);

        if (!$form) {
            abort(404, 'KYC record not found.');
        }

        return view('ccu.show', compact('form'));
    }

    /**
     * KYC Requests List
     */
    public function viewRequests()
    {
        $requests = KycForm::orderByDesc('created_at')->get();
        return view('ccu.requests', compact('requests'));
    }

    /**
     * Dashboard card data
     */
    public function cardData($type)
    {
        switch ($type) {
            case 'applications':
                $data = KycForm::get();
                break;

            case 'completed':
                $data = KycForm::where('billing_status', 'approved')->get();
                break;

            case 'pending':
                $data = KycForm::where('billing_status', 'pending')->get();
                break;

            case 'rkam':
                // Option 1: Direct SQL fallback for RKAM counts
                $data = DB::table('kyc_forms')->where('rkam_status','pending')->get();
                break;

            case 'bm':
                // Option 1: Direct SQL fallback for BM counts
                $data = DB::table('kyc_forms')->where('bm_status','pending')->get();
                break;

            default:
                $data = [];
        }

        return response()->json($data);
    }

    /**
     * Main CCU Dashboard
     */
    public function index(Request $request)
    {
        $page = (int) $request->get('page', 1);
        $pageSize = 50;
        $staff = auth()->user();
        $staffLocation = trim(preg_replace('/^.*,\s*(.*)$/i', '$1', $staff->location ?? ''));
        $staffStateLower = strtolower($staffLocation);

        // --- RKAM forms from SP with total count ---
        $procCall = "DECLARE @TotalCount INT;
                     EXEC sp_get_rkam_dashboard @PageNumber = ?, @PageSize = ?, @TotalCount = @TotalCount OUTPUT;
                     SELECT @TotalCount AS TotalCount;";

        $results = DB::connection('sqlsrv')->select($procCall, [$page, $pageSize]);
        $kycFormsRaw = collect($results);
        $totalRow = $kycFormsRaw->firstWhere('TotalCount');
        $totalCount = $totalRow->TotalCount ?? 0;
        if ($totalRow) {
            $kycFormsRaw = $kycFormsRaw->reject(fn($r) => isset($r->TotalCount));
        }
        $kycForms = $kycFormsRaw->values();

        // --- Location filter (skip HQ) ---
        if ($staffStateLower !== 'headquarter') {
            $kycForms = $kycForms->filter(fn($form) =>
                str_contains(strtolower($form->state ?? ''), $staffStateLower) ||
                str_contains(strtolower($form->new_address ?? ''), $staffStateLower)
            )->values();
            $totalCount = $kycForms->count();
        }

        // --- Map old customer data ---
        $prepaidAccounts = [];
        $postpaidAccounts = [];
        foreach ($kycForms as $form) {
            if ($form->account_type === 'prepaid') {
                $prepaidAccounts[] = $form->account_id;
            } else {
                $postpaidAccounts[] = $form->account_id;
            }
        }

        $prepaidCustomers = [];
        if (!empty($prepaidAccounts)) {
            try {
                $prepaidCustomers = DB::connection('sqlsrv81')->table('Customers')
                    ->whereIn('MeterNo', $prepaidAccounts)
                    ->get()->keyBy('MeterNo')->toArray();
            } catch (\Exception $e) {
                Log::warning('prepaid customer lookup failed: ' . $e->getMessage());
            }
        }

        $postpaidCustomers = [];
        if (!empty($postpaidAccounts)) {
            try {
                $postpaidCustomers = DB::connection('sqlsrv89')->table('CustomerNew')
                    ->whereIn('AccountNo', $postpaidAccounts)
                    ->get()->keyBy('AccountNo')->toArray();
            } catch (\Exception $e) {
                Log::warning('postpaid customer lookup failed: ' . $e->getMessage());
            }
        }

        foreach ($kycForms as $form) {
            $form->old_fullname = '-';
            $form->old_address = '-';
            $form->old_email = '-';
            $form->old_phone = '-';

            if ($form->account_type === 'prepaid' && isset($prepaidCustomers[$form->account_id])) {
                $old = $prepaidCustomers[$form->account_id];
                $form->old_fullname = $old->Fullname ?? '-';
                $form->old_address = $old->Address ?? '-';
                $form->old_email = $old->Email ?? '-';
                $form->old_phone = $old->Phone ?? '-';
            } elseif ($form->account_type !== 'prepaid' && isset($postpaidCustomers[$form->account_id])) {
                $old = $postpaidCustomers[$form->account_id];
                $form->old_fullname = trim(($old->Surname ?? '') . ' ' . ($old->FirstName ?? '')) ?: '-';
                $form->old_address = trim(($old->Address1 ?? '') . ' ' . ($old->Address2 ?? '')) ?: '-';
                $form->old_email = $old->email ?? '-';
                $form->old_phone = $old->Mobile ?? '-';
            }

            $form->submitted_at = $form->created_at;
            $form->created_at = Carbon::parse($form->created_at);
        }

        $paginator = new LengthAwarePaginator(
            $kycForms->forPage($page, $pageSize),
            $totalCount,
            $pageSize,
            $page,
            ['path' => url()->current(), 'query' => request()->query()]
        );

        // --- Dashboard counts ---
       $rkamCounts = DB::connection('sqlsrv')->selectOne("
    DECLARE @TotalPending INT;
    DECLARE @TotalApproved INT;
    DECLARE @TotalRejected INT;

    SELECT 
        @TotalPending = COUNT(*) FROM kyc_forms WHERE rkam_status = 'pending',
        @TotalApproved = COUNT(*) FROM kyc_forms WHERE rkam_status = 'approved',
        @TotalRejected = COUNT(*) FROM kyc_forms WHERE rkam_status = 'rejected';

    SELECT @TotalPending AS pending, @TotalApproved AS approved, @TotalRejected AS rejected;
");


        $bmCounts = (object) [
            'pending' => DB::table('kyc_forms')->where('bm_status','pending')->count(),
            'approved' => DB::table('kyc_forms')->where('bm_status','approved')->count(),
            'rejected' => DB::table('kyc_forms')->where('bm_status','rejected')->count(),
        ];

        $ricoCounts = (object) [
            'pending' => DB::table('kyc_forms')->where('rico_status', 'pending')->count(),
            'approved' => DB::table('kyc_forms')->where('rico_status', 'approved')->count(),
            'rejected' => DB::table('kyc_forms')->where('rico_status', 'rejected')->count(),
        ];

        $billingCounts = (object) [
            'pending' => DB::table('kyc_forms')->where('billing_status', 'pending')->count(),
            'approved' => DB::table('kyc_forms')->where('billing_status', 'approved')->count(),
            'rejected' => DB::table('kyc_forms')->where('billing_status', 'rejected')->count(),
        ];

        $stageCounts = [
            'RKAM' => (array) $rkamCounts,
            'BM' => (array) $bmCounts,
            'RICO' => (array) $ricoCounts,
            'Billing' => (array) $billingCounts,
        ];

        $totals = [
            'started' => DB::table('kyc_forms')->count(),
            'approved' => $billingCounts->approved,
            'pending' => $rkamCounts->pending + $bmCounts->pending + $ricoCounts->pending + $billingCounts->pending,
            'rejected' => $rkamCounts->rejected + $bmCounts->rejected + $ricoCounts->rejected + $billingCounts->rejected,
        ];

        return view('ccu.dashboard', [
            'kycForms' => $paginator,
            'stageCounts' => $stageCounts,
            'totals' => $totals,
            'currentPage' => $page,
            'pageSize' => $pageSize,
            'totalCount' => $totalCount,
        ]);
    }

    /**
     * CSV Export
     */
    public function export(Request $request)
    {
        $query = DB::connection('sqlsrv')->table('kyc_forms');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('meter_no', 'like', "%{$search}%")
                  ->orWhere('account_id', 'like', "%{$search}%")
                  ->orWhere('nin', 'like', "%{$search}%")
                  ->orWhere('fullname', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $kycForms = $query->latest('created_at')->get();

        // Map old customer data same as index
        $prepaidAccounts = [];
        $postpaidAccounts = [];
        foreach ($kycForms as $form) {
            if ($form->account_type === 'prepaid') {
                $prepaidAccounts[] = $form->account_id;
            } else {
                $postpaidAccounts[] = $form->account_id;
            }
        }

        $prepaidCustomers = [];
        if (!empty($prepaidAccounts)) {
            $prepaidCustomers = DB::connection('sqlsrv81')->table('Customers')
                ->whereIn('MeterNo', $prepaidAccounts)
                ->get()->keyBy('MeterNo')->toArray();
        }

        $postpaidCustomers = [];
        if (!empty($postpaidAccounts)) {
            $postpaidCustomers = DB::connection('sqlsrv89')->table('CustomerNew')
                ->whereIn('AccountNo', $postpaidAccounts)
                ->get()->keyBy('AccountNo')->toArray();
        }

        $filename = 'kyc_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($kycForms, $prepaidCustomers, $postpaidCustomers) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Submission Date','Account Type','Account ID','Meter No','NIN',
                'Old Name','New Name','Old Address','New Address','Old Email','New Email',
                'Old Phone','New Phone','State','RKAM Status','BM Status','RICO Status','Billing Status'
            ]);

            foreach ($kycForms as $form) {
                $old_fullname = '-';
                $old_address = '-';
                $old_email = '-';
                $old_phone = '-';

                if ($form->account_type === 'prepaid' && isset($prepaidCustomers[$form->account_id])) {
                    $old = $prepaidCustomers[$form->account_id];
                    $old_fullname = $old->Fullname ?? '-';
                    $old_address = $old->Address ?? '-';
                    $old_email = $old->Email ?? '-';
                    $old_phone = $old->Phone ?? '-';
                } elseif ($form->account_type !== 'prepaid' && isset($postpaidCustomers[$form->account_id])) {
                    $old = $postpaidCustomers[$form->account_id];
                    $old_fullname = trim(($old->Surname ?? '') . ' ' . ($old->FirstName ?? '')) ?: '-';
                    $old_address = trim(($old->Address1 ?? '') . ' ' . ($old->Address2 ?? '')) ?: '-';
                    $old_email = $old->email ?? '-';
                    $old_phone = $old->Mobile ?? '-';
                }

                fputcsv($file, [
                    Carbon::parse($form->created_at)->format('Y-m-d H:i:s'),
                    $form->account_type ?? '-',
                    $form->account_id ?? '-',
                    $form->meter_no ?? '-',
                    $form->nin ?? '-',
                    $old_fullname,
                    $form->fullname ?? '-',
                    $old_address,
                    $form->address ?? '-',
                    $old_email,
                    $form->email ?? '-',
                    $old_phone,
                    $form->phone ?? '-',
                    $form->state ?? '-',
                    $form->rkam_status ?? '-',
                    $form->bm_status ?? '-',
                    $form->rico_status ?? '-',
                    $form->billing_status ?? '-'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get CCU Forms via SP
     */
    public function getForms(Request $request)
    {
        $stage  = $request->input('stage');
        $status = $request->input('status');
        $search = $request->input('search');

        $forms = DB::select("EXEC sp_getCCUPendingForms :stage, :status, :search", [
            'stage'  => $stage,
            'status' => $status,
            'search' => $search,
        ]);

        return response()->json($forms);
    }
}
