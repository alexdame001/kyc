<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class CCUController2 extends Controller
{
    public function dashboard(Request $request)
    {
        $page = (int) $request->get('page', 1);
        $pageSize = 50;
        $currentStageFilter = $request->get('stage', null);

        // --- Get RKAM forms using SP ---
        $rkamResults = DB::connection('sqlsrv')->select("EXEC sp_get_rkam_dashboard");
        $rkamForms = collect($rkamResults);

        // --- Get BM forms using SP ---
        $bmResults = DB::connection('sqlsrv')->select("EXEC sp_get_bm_dashboard");
        $bmForms = collect($bmResults);

        // --- RICO: rkam or bm approved, rico_status pending ---
        $ricoFormsQuery = DB::table('kyc_forms')
            ->where('rico_status', 'pending')
            ->where(function($q) {
                $q->where('rkam_status', 'approved')
                  ->orWhere('bm_status', 'approved');
            });

        // --- Billing: rico approved, billing_status pending ---
        $billingFormsQuery = DB::table('kyc_forms')
            ->where('billing_status', 'pending')
            ->where('rico_status', 'approved');

        // --- Combine all KYC forms for table ---
        $kycFormsQuery = DB::table('kyc_forms');

        if ($currentStageFilter) {
            switch($currentStageFilter) {
                case 'RKAM':
                    $kycFormsQuery = $kycFormsQuery->whereIn('id', $rkamForms->pluck('id'));
                    break;
                case 'BM':
                    $kycFormsQuery = $kycFormsQuery->whereIn('id', $bmForms->pluck('id'));
                    break;
                case 'RICO':
                    $kycFormsQuery = $kycFormsQuery->whereIn('id', $ricoFormsQuery->pluck('id'));
                    break;
                case 'Billing':
                    $kycFormsQuery = $kycFormsQuery->whereIn('id', $billingFormsQuery->pluck('id'));
                    break;
            }
        }

        $totalCount = $kycFormsQuery->count();
        $kycForms = $kycFormsQuery
            ->orderByDesc('submitted_at')
            ->forPage($page, $pageSize)
            ->get();

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
                Log::warning('Prepaid customer lookup failed: ' . $e->getMessage());
            }
        }

        $postpaidCustomers = [];
        if (!empty($postpaidAccounts)) {
            try {
                $postpaidCustomers = DB::connection('sqlsrv89')->table('CustomerNew')
                    ->whereIn('AccountNo', $postpaidAccounts)
                    ->get()->keyBy('AccountNo')->toArray();
            } catch (\Exception $e) {
                Log::warning('Postpaid customer lookup failed: ' . $e->getMessage());
            }
        }

        // --- Fill old data for table ---
        foreach ($kycForms as $form) {
            $form->old_name = '-';
            $form->old_address = '-';
            $form->old_email = '-';
            $form->old_phone = '-';

            if ($form->account_type === 'prepaid' && isset($prepaidCustomers[$form->account_id])) {
                $old = $prepaidCustomers[$form->account_id];
                $form->old_name = $old->Fullname ?? '-';
                $form->old_address = $old->Address ?? '-';
                $form->old_email = $old->Email ?? '-';
                $form->old_phone = $old->Phone ?? '-';
            } elseif ($form->account_type !== 'prepaid' && isset($postpaidCustomers[$form->account_id])) {
                $old = $postpaidCustomers[$form->account_id];
                $form->old_name = trim(($old->Surname ?? '') . ' ' . ($old->FirstName ?? '')) ?: '-';
                $form->old_address = trim(($old->Address1 ?? '') . ' ' . ($old->Address2 ?? '')) ?: '-';
                $form->old_email = $old->email ?? '-';
                $form->old_phone = $old->Mobile ?? '-';
            }

            $form->current_stage = $this->getCurrentStage($form);
        }

        // --- Pagination ---
        $paginator = new LengthAwarePaginator(
            $kycForms,
            $totalCount,
            $pageSize,
            $page,
            ['path' => url()->current(), 'query' => request()->query()]
        );

        // --- Stage counts ---
        $stageCounts = [
            'RKAM' => [
                'pending' => $rkamForms->where('rkam_status','pending')->count(),
                'approved' => $rkamForms->where('rkam_status','approved')->count(),
                'rejected' => $rkamForms->where('rkam_status','rejected')->count(),
            ],
            'BM' => [
                'pending' => $bmForms->where('bm_status','pending')->count(),
                'approved' => $bmForms->where('bm_status','approved')->count(),
                'rejected' => $bmForms->where('bm_status','rejected')->count(),
            ],
            'RICO' => [
                'pending' => $ricoFormsQuery->count(),
                'approved' => DB::table('kyc_forms')->where('rico_status','approved')->count(),
                'rejected' => DB::table('kyc_forms')->where('rico_status','rejected')->count(),
            ],
            'Billing' => [
                'pending' => $billingFormsQuery->count(),
                'approved' => DB::table('kyc_forms')->where('billing_status','approved')->count(),
                'rejected' => DB::table('kyc_forms')->where('billing_status','rejected')->count(),
            ]
        ];

        return view('ccu.dashboard2', [
            'kycForms' => $paginator,
            'stageCounts' => $stageCounts,
            'currentStageFilter' => $currentStageFilter
        ]);
    }

    private function getCurrentStage($form)
    {
        if ($form->billing_status == 'pending') return 'Billing';
        if ($form->rico_status == 'pending') return 'RICO';
        if ($form->bm_status == 'pending') return 'BM';
        if ($form->rkam_status == 'pending') return 'RKAM';
        return 'Completed';
    }

    public function show($id)
    {
        $form = DB::table('kyc_forms')->find($id);
        if (!$form) abort(404);
        return view('ccu.show', compact('form'));
    }
}
