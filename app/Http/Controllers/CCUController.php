<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;

class CCUController extends Controller
{

public function exportCsvPage(Request $request)
{
    $filter = $request->get('filter', 'all');
    $search = $request->get('search');
    $perPage = 25;
    $page = $request->get('page', 1);
    $offset = ($page - 1) * $perPage;

    $query = DB::table('kyc_forms as k')
        ->select([
            'k.id', 'k.account_id', 'k.account_type', 'k.submitted_at', 'k.fullname',
            'k.address', 'k.email', 'k.phone', 'k.nin',
            'k.rkam_status', 'k.bm_status', 'k.rico_status', 'k.billing_status'
        ])
        ->orderBy('k.submitted_at', 'desc');

    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('k.account_id', 'like', "%$search%")
              ->orWhere('k.fullname', 'like', "%$search%")
              ->orWhere('k.phone', 'like', "%$search%")
              ->orWhere('k.nin', 'like', "%$search%")
              ->orWhere('k.email', 'like', "%$search%");
        });
    }

    // Apply same filters as dashboard
    switch ($filter) {
        case 'rkam': $query->where('k.rkam_status', 'pending'); break;
        case 'bm': $query->where('k.bm_status', 'pending'); break;
        case 'rico':
            $query->where(function($q) {
                $q->where('k.rkam_status', 'approved')->orWhere('k.bm_status', 'approved');
            })->where(function($q) {
                $q->whereNull('k.rico_status')->orWhere('k.rico_status', 'pending')->orWhere('k.rico_status', '');
            });
            break;
        case 'billing':
            $query->where('k.rico_status', 'approved')->where(function($q) {
                $q->whereNull('k.billing_status')->orWhere('k.billing_status', 'pending')->orWhere('k.billing_status', '');
            });
            break;
        case 'pending':
            $query->where(function($q) {
                $q->whereNull('k.billing_status')->orWhere('k.billing_status', 'pending');
            });
            break;
        case 'approved': $query->where('k.billing_status', 'approved'); break;
        case 'rejected':
            $query->whereRaw("(k.rkam_status='rejected' OR k.bm_status='rejected' OR k.rico_status='rejected' OR k.billing_status='rejected')");
            break;
    }

    $forms = $query->skip($offset)->take($perPage)->get();

    // Fetch old data
    $prepaidIds = $forms->where('account_type', 'prepaid')->pluck('account_id')->toArray();
    $postpaidIds = $forms->where('account_type', '!=', 'prepaid')->pluck('account_id')->toArray();

    $prepaidOld = !empty($prepaidIds)
        ? DB::connection('sqlsrv')->table('ECMI_Customers_Snapshot')->whereIn('MeterNo', $prepaidIds)->get()->keyBy('MeterNo')
        : collect();

    $postpaidOld = !empty($postpaidIds)
        ? DB::connection('sqlsrv')->table('EMS_CustomerNew_Snapshot')->whereIn('AccountNo', $postpaidIds)->get()->keyBy('AccountNo')
        : collect();

    $fileName = 'kyc_page_' . now()->format('Ymd_His') . '.csv';
    $headers = [
        "Content-type"        => "text/csv",
        "Content-Disposition" => "attachment; filename={$fileName}",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        "Expires"             => "0"
    ];

    return response()->stream(function() use ($forms, $prepaidOld, $postpaidOld) {
        $file = fopen('php://output', 'w');
        fputcsv($file, [
            'Account ID', 'Old Name', 'New Name', 'Old Address', 'New Address',
            'Old Email', 'New Email', 'Old Phone', 'New Phone', 'Account Type',
            'Submitted At', 'Current Stage'
        ]);

        foreach ($forms as $f) {
            $old = $f->account_type === 'prepaid'
                ? ($prepaidOld[$f->account_id] ?? null)
                : ($postpaidOld[$f->account_id] ?? null);

            $currentStage = match(true) {
                $f->rkam_status === 'pending' => 'RKAM Review',
                $f->bm_status === 'pending' => 'BM Review',
                ($f->rkam_status === 'approved' || $f->bm_status === 'approved') 
                    && (is_null($f->rico_status) || $f->rico_status === 'pending' || $f->rico_status === '') => 'RICO Review',
                $f->rico_status === 'approved' && (is_null($f->billing_status) || $f->billing_status === 'pending' || $f->billing_status === '') => 'Billing Review',
                $f->billing_status === 'approved' => 'Approved',
                default => 'Rejected'
            };

            fputcsv($file, [
                $f->account_id,
                $old ? trim(($old->Surname ?? $old->OtherNames ?? '') . ' ' . ($old->FirstName ?? $old->Surname ?? '')) : '-',
                $f->fullname,
                $old ? trim(($old->Address1 ?? $old->Address ?? '') . ' ' . ($old->Address2 ?? '')) : '-',
                $f->address ?? '-',
                $old?->Email ?? '-',
                $f->email ?? '-',
                $old?->Mobile ?? $old?->Phone ?? '-',
                $f->phone ?? '-',
                $f->account_type,
                $f->submitted_at,
                $currentStage
            ]);
        }

        fclose($file);
    }, 200, $headers);
}

public function exportCsvAll(Request $request)
{
    $filter = $request->get('filter', 'all');
    $search = $request->get('search');

    $fileName = 'kyc_all_' . now()->format('Ymd_His') . '.csv';
    $headers = [
        "Content-type"        => "text/csv",
        "Content-Disposition" => "attachment; filename={$fileName}",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        "Expires"             => "0"
    ];

    return response()->stream(function() use ($filter, $search) {
        $file = fopen('php://output', 'w');

        // CSV header
        fputcsv($file, [
            'Account ID', 'Old Name', 'New Name', 'Old Address', 'New Address',
            'Old Email', 'New Email', 'Old Phone', 'New Phone', 'Account Type',
            'Submitted At', 'Current Stage'
        ]);

        $chunkSize = 1000; // fetch 1000 rows at a time
        DB::table('kyc_forms as k')
            ->select([
                'k.id', 'k.account_id', 'k.account_type', 'k.submitted_at', 'k.fullname',
                'k.address', 'k.email', 'k.phone', 'k.nin',
                'k.rkam_status', 'k.bm_status', 'k.rico_status', 'k.billing_status'
            ])
            ->when($search, function($q) use ($search) {
                $q->where(function($q) use ($search) {
                    $q->where('k.account_id', 'like', "%$search%")
                      ->orWhere('k.fullname', 'like', "%$search%")
                      ->orWhere('k.phone', 'like', "%$search%")
                      ->orWhere('k.nin', 'like', "%$search%")
                      ->orWhere('k.email', 'like', "%$search%");
                });
            })
            ->when($filter, function($q) use ($filter) {
                switch ($filter) {
                    case 'rkam': $q->where('k.rkam_status', 'pending'); break;
                    case 'bm': $q->where('k.bm_status', 'pending'); break;
                    case 'rico':
                        $q->where(function($q) {
                            $q->where('k.rkam_status', 'approved')->orWhere('k.bm_status', 'approved');
                        })->where(function($q) {
                            $q->whereNull('k.rico_status')->orWhere('k.rico_status', 'pending')->orWhere('k.rico_status', '');
                        });
                        break;
                    case 'billing':
                        $q->where('k.rico_status', 'approved')->where(function($q) {
                            $q->whereNull('k.billing_status')->orWhere('k.billing_status', 'pending')->orWhere('k.billing_status', '');
                        });
                        break;
                    case 'pending':
                        $q->where(function($q) {
                            $q->whereNull('k.billing_status')->orWhere('k.billing_status', 'pending');
                        });
                        break;
                    case 'approved': $q->where('k.billing_status', 'approved'); break;
                    case 'rejected':
                        $q->whereRaw("(k.rkam_status='rejected' OR k.bm_status='rejected' OR k.rico_status='rejected' OR k.billing_status='rejected')");
                        break;
                }
            })
            ->orderBy('k.submitted_at', 'desc')
            ->chunk($chunkSize, function($forms) use ($file) {
                // Fetch old data
                $prepaidIds = $forms->where('account_type', 'prepaid')->pluck('account_id')->toArray();
                $postpaidIds = $forms->where('account_type', '!=', 'prepaid')->pluck('account_id')->toArray();

                $prepaidOld = !empty($prepaidIds)
                    ? DB::connection('sqlsrv')->table('ECMI_Customers_Snapshot')->whereIn('MeterNo', $prepaidIds)->get()->keyBy('MeterNo')
                    : collect();

                $postpaidOld = !empty($postpaidIds)
                    ? DB::connection('sqlsrv')->table('EMS_CustomerNew_Snapshot')->whereIn('AccountNo', $postpaidIds)->get()->keyBy('AccountNo')
                    : collect();

                foreach ($forms as $f) {
                    $old = $f->account_type === 'prepaid'
                        ? ($prepaidOld[$f->account_id] ?? null)
                        : ($postpaidOld[$f->account_id] ?? null);

                    $currentStage = match(true) {
                        $f->rkam_status === 'pending' => 'RKAM Review',
                        $f->bm_status === 'pending' => 'BM Review',
                        ($f->rkam_status === 'approved' || $f->bm_status === 'approved') 
                            && (is_null($f->rico_status) || $f->rico_status === 'pending' || $f->rico_status === '') => 'RICO Review',
                        $f->rico_status === 'approved' && (is_null($f->billing_status) || $f->billing_status === 'pending' || $f->billing_status === '') => 'Billing Review',
                        $f->billing_status === 'approved' => 'Approved',
                        default => 'Rejected'
                    };

                    fputcsv($file, [
                        $f->account_id,
                        $old ? trim(($old->Surname ?? $old->OtherNames ?? '') . ' ' . ($old->FirstName ?? $old->Surname ?? '')) : '-',
                        $f->fullname,
                        $old ? trim(($old->Address1 ?? $old->Address ?? '') . ' ' . ($old->Address2 ?? '')) : '-',
                        $f->address ?? '-',
                        $old?->Email ?? '-',
                        $f->email ?? '-',
                        $old?->Mobile ?? $old?->Phone ?? '-',
                        $f->phone ?? '-',
                        $f->account_type,
                        $f->submitted_at,
                        $currentStage
                    ]);
                }
            });

        fclose($file);
    }, 200, $headers);
}



public function dashboard(Request $request)
{
    $filter = $request->get('filter', 'all');
    $search = $request->get('search');

    // Manual cache refresh
    if ($request->has('refresh')) {
        Cache::forget('ccu_summary_counts');
        return redirect()->route('ccu.dashboard')->with('status', 'Counts refreshed!');
    }

    // Fast cached counts (RKAM from job)
    $counts = Cache::remember('ccu_summary_counts', 300, function () {
        $rkamRow = DB::selectOne("EXEC sp_get_rkam_pending_count");
        $bmRow = DB::selectOne("EXEC sp_get_bm_pending_count");

        return [
            'with_rkam' => $rkamRow->rkam_pending ?? 0,
            'with_bm' => $bmRow->bm_pending ?? 0,
            'with_rico' => DB::table('kyc_forms')
                ->where(fn($q) => $q->where('rkam_status', 'approved')->orWhere('bm_status', 'approved'))
                ->where(fn($q) => $q->whereNull('rico_status')->orWhere('rico_status', 'pending')->orWhere('rico_status', ''))
                ->count(),
            'with_billing' => DB::table('kyc_forms')
                ->where('rico_status', 'approved')
                ->where(fn($q) => $q->whereNull('billing_status')->orWhere('billing_status', 'pending')->orWhere('billing_status', ''))
                ->count(),
            'total_applications' => DB::table('kyc_forms')->count(),
            'total_pending' => DB::table('kyc_forms')
                ->where(fn($q) => $q->whereNull('billing_status')->orWhere('billing_status', 'pending'))
                ->where('rico_status', '!=', 'rejected')
                ->where('rkam_status', '!=', 'rejected')
                ->where('bm_status', '!=', 'rejected')
                ->count(),
            'total_approved' => DB::table('kyc_forms')->where('billing_status', 'approved')->count(),
            'total_rejected' => DB::table('kyc_forms')
                ->where(fn($q) => $q->where('rkam_status', 'rejected')
                                  ->orWhere('bm_status', 'rejected')
                                  ->orWhere('rico_status', 'rejected')
                                  ->orWhere('billing_status', 'rejected'))
                ->count(),
        ];
    });

    // Base query
    $query = DB::table('kyc_forms as k')
        ->select([
            'k.id',
            'k.account_id',
            'k.account_type',
            'k.submitted_at',
            'k.fullname',
            'k.address',
            'k.email',
            'k.phone',
            'k.nin',
            'k.state',
            'k.document_path',
            'k.rkam_status',
            'k.bm_status',
            'k.rico_status',
            'k.billing_status',
            DB::raw("
                CASE
                    WHEN '{$filter}' = 'rkam' THEN 'RKAM Review'
                    WHEN '{$filter}' = 'bm' THEN 'BM Review'
                    WHEN '{$filter}' = 'rico' THEN 'RICO Review'
                    WHEN '{$filter}' = 'billing' THEN 'Billing Review'
                    WHEN k.billing_status = 'approved' THEN 'Approved'
                    ELSE 'Click respective cards above to check stages'
                END AS current_stage
            "),
            DB::raw("
                CASE 
                    WHEN k.submitted_at > GETDATE() THEN 0 
                    ELSE DATEDIFF(hour, k.submitted_at, GETDATE()) 
                END as age_hours
            "),
            'u.email as responsible_email' // Fetch email from users table
        ])
        ->leftJoin('users as u', function($join) {
            $join->on('k.responsible_staff_id', '=', 'u.id');
        })
        ->orderBy('k.submitted_at', 'desc');

    // Global search
    if ($search) {
        $query->where(fn($q) => $q->where('k.account_id', 'like', "%$search%")
                                  ->orWhere('k.fullname', 'like', "%$search%")
                                  ->orWhere('k.phone', 'like', "%$search%")
                                  ->orWhere('k.nin', 'like', "%$search%")
                                  ->orWhere('k.email', 'like', "%$search%"));
    }

    // Filters
    switch ($filter) {
        case 'rkam':
            $query->where('k.rkam_status', 'pending')
                  ->whereRaw("k.account_id IN (
                      SELECT AccountNo COLLATE SQL_Latin1_General_CP1_CI_AS 
                      FROM [sqlsrv89].[EMS_Zone].dbo.CustomerNew 
                      WHERE TariffID IN (6,11,13,19,10,5,14,17)
                      UNION
                      SELECT MeterNo COLLATE SQL_Latin1_General_CP1_CI_AS 
                      FROM [sqlsrv81].[ECMI].dbo.Customers 
                      WHERE TariffID IN (6,11,13,19,10,5,14,17)
                  )");
            break;
        case 'bm':
            $query->where('k.bm_status', 'pending');
            break;
        case 'rico':
            $query->where(fn($q) => $q->where('k.rkam_status', 'approved')->orWhere('k.bm_status', 'approved'))
                  ->where(fn($q) => $q->whereNull('k.rico_status')->orWhere('k.rico_status', 'pending')->orWhere('k.rico_status', ''));
            break;
        case 'billing':
            $query->where('k.rico_status', 'approved')
                  ->where(fn($q) => $q->whereNull('k.billing_status')->orWhere('k.billing_status', 'pending')->orWhere('k.billing_status', ''));
            break;
        case 'pending':
            $query->where(fn($q) => $q->whereNull('k.billing_status')->orWhere('k.billing_status', 'pending'));
            break;
        case 'approved':
            $query->where('k.billing_status', 'approved');
            break;
        case 'rejected':
            $query->whereRaw("(k.rkam_status='rejected' OR k.bm_status='rejected' OR k.rico_status='rejected' OR k.billing_status='rejected')");
            break;
    }

    // Pagination
    $perPage = 25;
    $page = $request->get('page', 1);
    $offset = ($page * $perPage) - $perPage;
    $total = $query->count();

    $forms = $query->skip($offset)->take($perPage)->get();

    // Fetch Old Data
    $prepaidIds = $forms->where('account_type', 'prepaid')->pluck('account_id')->toArray();
    $postpaidIds = $forms->where('account_type', '!=', 'prepaid')->pluck('account_id')->toArray();

    $prepaidOld = !empty($prepaidIds)
        ? DB::connection('sqlsrv')->table('ECMI_Customers_Snapshot')
            ->whereIn('MeterNo', $prepaidIds)->get()->keyBy('MeterNo')
        : collect();

    $postpaidOld = !empty($postpaidIds)
        ? DB::connection('sqlsrv')->table('EMS_CustomerNew_Snapshot')
            ->whereIn('AccountNo', $postpaidIds)->get()->keyBy('AccountNo')
        : collect();

    foreach ($forms as $form) {
        $old = $form->account_type === 'prepaid' 
            ? ($prepaidOld[$form->account_id] ?? null)
            : ($postpaidOld[$form->account_id] ?? null);

        $form->old_fullname = $old ? trim(($old->Surname ?? $old->OtherNames ?? '') . ' ' . ($old->FirstName ?? $old->Surname ?? '')) : '-';
        $form->old_address  = $old ? trim(($old->Address1 ?? $old->Address ?? '') . ' ' . ($old->Address2 ?? '')) : '-';
        $form->old_email    = $old?->Email ?? '-';
        $form->old_phone    = $old?->Mobile ?? $old?->Phone ?? '-';
    }

    // Manual paginator
    $forms = new LengthAwarePaginator($forms, $total, $perPage, $page, [
        'path' => $request->url(),
        'query' => $request->query(),
    ]);

    return view('dashboard.ccu', compact('counts', 'forms', 'filter'));
}





}



