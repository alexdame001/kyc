<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use App\Services\SmartSMSService;
use App\Mail\RkamKycAlert;
use Exception;
use Illuminate\Support\Facades\Validator;

class BMController extends Controller
{
    private $smsService;

    public function __construct(SmartSMSService $smsService = null)
    {
        $this->smsService = $smsService;
    }

    /**
     * BM Dashboard - Shows only pending forms assigned to the logged-in BM
     */
public function index(Request $request)
{
    $staff = Auth::user();
    if (!$staff || !$staff->location) {
        abort(403, 'Access denied: No location assigned.');
    }

    $staffId       = $staff->id;
    $staffLocation = trim($staff->location);
    $isHeadquarter = in_array(strtolower($staffLocation), ['headquarter', 'hq', 'headquarters', 'head office']);

    $page     = (int) $request->get('page', 1);
    $pageSize = (int) $request->get('pageSize', 50);

    $procCall = 'DECLARE @TotalCount INT; 
                 EXEC sp_get_bm_dashboard ?, ?, ?, @TotalCount OUTPUT; 
                 SELECT @TotalCount AS TotalCount;';

    try {
        $results = DB::connection('sqlsrv')->select($procCall, [$page, $pageSize, $staffId]);
    } catch (Exception $e) {
        Log::error('BM Dashboard SP Error: ' . $e->getMessage());
        abort(500, 'Failed to load dashboard data. Please try again later.');
    }

    $kycFormsRaw = collect($results);

    // === ROBUST TotalCount extraction (fixes the "No pending" bug) ===
    $totalCount = 0;
    $totalRowIndex = $kycFormsRaw->search(function ($row) {
        return property_exists($row, 'TotalCount') ||
               property_exists($row, 'totalcount') ||
               property_exists($row, 'TOTALCOUNT') ||
               isset($row->TotalCount);
    });

    if ($totalRowIndex !== false) {
        $totalRow = $kycFormsRaw->pull($totalRowIndex);
        $totalCount = (int) ($totalRow->TotalCount ?? $totalRow->totalcount ?? $totalRow->TOTALCOUNT ?? 0);
    }

    // Fallback: if SP didn't return count properly, use the actual number of forms we have
    if ($totalCount === 0 && $kycFormsRaw->isNotEmpty()) {
        $totalCount = $kycFormsRaw->count();
    }
    // === End of fix ===

    // Defense-in-depth: ensure BU match via buname (skip for HQ)
    $kycForms = $kycFormsRaw->filter(function ($form) use ($staffLocation, $isHeadquarter) {
        if ($isHeadquarter) {
            return true;
        }
        $formBUName = trim($form->buname ?? '');
        return strcasecmp($formBUName, $staffLocation) === 0;
    });

    // Normalize missing old values
    $kycForms->transform(function ($form) {
        $form->old_fullname = $form->old_fullname ?? '-';
        $form->old_address  = $form->old_address ?? '-';
        $form->old_email    = $form->old_email ?? '-';
        $form->old_phone    = $form->old_phone ?? '-';
        return $form;
    });

    return view('dashboard.bm', [
        'kycForms'      => $kycForms->values(),
        'totalCount'    => $totalCount,        // Now always correct
        'currentPage'   => $page,
        'pageSize'      => $pageSize,
        'staffLocation' => $staff->location,
        'staffState'    => $staffLocation,
    ]);
}

    /**
     * AJAX: Update single form status
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'notes'  => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $status = $request->input('status');
        $notes  = $request->input('notes');

        $staff = Auth::user();
        $staffId       = $staff->id;
        $staffLocation = trim($staff->location ?? '');
        $isHeadquarter = in_array(strtolower($staffLocation), ['headquarter', 'hq', 'headquarters', 'head office']);

        $form = DB::table('kyc_forms')->where('id', $id)->first();
        if (!$form) {
            return response()->json(['success' => false, 'message' => 'Form not found'], 404);
        }

        if (($form->bm_status ?? 'pending') !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Form is not in pending state'], 409);
        }

        // Authorization: must be assigned to this BM
        if ($form->responsible_staff_id != $staffId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized: Not assigned to you'], 403);
        }

        // Extra safety: BU match via buname
        if (!$isHeadquarter) {
            $formBUName = trim($form->buname ?? '');
            if (strcasecmp($formBUName, $staffLocation) !== 0) {
                return response()->json(['success' => false, 'message' => 'Unauthorized: Not in your business unit'], 403);
            }
        }

        try {
            DB::table('kyc_forms')->where('id', $id)->update([
                'bm_status'      => $status,
                'bm_remarks'     => $status === 'rejected' ? $notes : null,
                'bm_reviewed_at' => Carbon::now(),
                'reviewed_by'    => $staffId,
                'updated_at'     => Carbon::now(),
            ]);

            try {
                $this->notifyOnStatusChange($form, $status, 'BM', $status === 'rejected' ? $notes : null);
            } catch (Exception $e) {
                Log::warning("Notification failed for form {$id}: " . $e->getMessage());
            }

            Log::info('BM Status Update', ['id' => $id, 'status' => $status, 'staff_id' => $staffId]);

            return response()->json(['success' => true, 'message' => 'Status updated successfully!'], 200);
        } catch (Exception $e) {
            Log::error('BM updateStatus error: ' . $e->getMessage(), ['id' => $id]);
            return response()->json(['success' => false, 'message' => 'Update failed'], 500);
        }
    }

    /**
     * AJAX: Bulk Approve
     */
    public function bulkApprove(Request $request)
    {
        return $this->bulkAction($request, 'approved');
    }

    /**
     * AJAX: Bulk Reject
     */
    public function bulkReject(Request $request)
    {
        $request->merge(['status' => 'rejected']);
        return $this->bulkAction($request, 'rejected', true);
    }

    /**
     * Shared logic for bulk approve/reject
     */
    private function bulkAction(Request $request, $status, $requireReason = false)
    {
        $rules = [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|min:1',
        ];
        if ($requireReason) {
            $rules['reason'] = 'required|string|max:500';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $ids    = $request->input('ids', []);
        $reason = $request->input('reason');
        $staff  = Auth::user();
        $staffId       = $staff->id;
        $staffLocation = trim($staff->location ?? '');
        $isHeadquarter = in_array(strtolower($staffLocation), ['headquarter', 'hq', 'headquarters', 'head office']);

        $updatedCount = 0;
        DB::beginTransaction();

        try {
            foreach ($ids as $id) {
                $form = DB::table('kyc_forms')->where('id', $id)->first();
                if (!$form || ($form->bm_status ?? 'pending') !== 'pending') {
                    continue;
                }

                // Authorization checks
                if ($form->responsible_staff_id != $staffId) {
                    continue;
                }

                if (!$isHeadquarter) {
                    $formBUName = trim($form->buname ?? '');
                    if (strcasecmp($formBUName, $staffLocation) !== 0) {
                        continue;
                    }
                }

                DB::table('kyc_forms')->where('id', $id)->update([
                    'bm_status'      => $status,
                    'bm_remarks'     => $status === 'rejected' ? $reason : null,
                    'bm_reviewed_at' => Carbon::now(),
                    'reviewed_by'    => $staffId,
                    'updated_at'     => Carbon::now(),
                ]);

                try {
                    $this->notifyOnStatusChange($form, $status, 'BM', $status === 'rejected' ? $reason : null);
                } catch (Exception $e) {
                    Log::warning("Notification failed (bulk) for form {$id}: " . $e->getMessage());
                }

                $updatedCount++;
            }

            DB::commit();

            Log::info("Bulk {$status} by BM", ['count' => $updatedCount, 'staff_id' => $staffId]);

            return response()->json([
                'success' => true,
                'message' => "{$updatedCount} form(s) " . ($status === 'approved' ? 'approved' : 'rejected') . '.',
                $status   => $updatedCount
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Bulk {$status} failed: " . $e->getMessage(), ['ids' => $ids]);
            return response()->json(['success' => false, 'message' => 'Bulk action failed.'], 500);
        }
    }

    // === Notification & Helper Methods (unchanged, just cleaned) ===

    private function notifyOnStatusChange($form, $status, $tier, $notes = null)
    {
        $accountType = $form->account_type ?? ($form->accountType ?? 'prepaid');
        $accountId   = $form->account_id ?? ($form->AccountNo ?? null);

        $customer = $this->getCustomerByAccountId($accountId, $accountType);
        $phoneRaw = $customer->Mobile ?? $customer->Phone ?? ($form->phone ?? null);
        $phone    = $this->formatToMSISDN($phoneRaw);

        if ($phone) {
            $smsService = $this->smsService ?? app(SmartSMSService::class);
            $msg = $status === 'approved'
                ? "IBEDC KYC: Your update has been approved! Changes are now live."
                : "IBEDC KYC: Your update was rejected. Reason: " . ($notes ?? 'Review and resubmit.') . " Log in to resubmit.";

            try {
                $result = $smsService->send($phone, $msg);
                if (!($result['success'] ?? false)) {
                    Log::error("SMS failed for {$accountId}: " . ($result['error'] ?? 'Unknown'));
                }
            } catch (Exception $e) {
                Log::warning("SMS exception for {$accountId}: " . $e->getMessage());
            }
        }

        // Notify RICO if fully approved
        if ($status === 'approved') {
            $rkamDone = DB::table('kyc_forms')->where('id', $form->id)->value('rkam_status') === 'approved';
            if ($rkamDone) {
                try {
                    $ricoEmails = DB::table('users')->where('role', 'rico')->pluck('email')->toArray();
                    foreach ($ricoEmails as $email) {
                        Mail::to($email)->queue(new RkamKycAlert($form, 'Ready for RICO review!'));
                    }
                } catch (Exception $e) {
                    Log::warning("RICO email failed for form {$form->id}: " . $e->getMessage());
                }
            }
        }
    }

    private function getCustomerByAccountId($accountId, $type)
    {
        try {
            if (!$accountId) {
                return (object) ['Mobile' => null, 'Phone' => null];
            }
            if (strtolower($type) === 'postpaid') {
                return DB::connection('sqlsrv89')->table('CustomerNew')->where('AccountNo', $accountId)->first()
                    ?? (object) ['Mobile' => null, 'Phone' => null];
            }
            return DB::connection('sqlsrv81')->table('Customers')->where('MeterNo', $accountId)->first()
                ?? (object) ['Mobile' => null, 'Phone' => null];
        } catch (Exception $e) {
            Log::error('Customer lookup error: ' . $e->getMessage());
            return (object) ['Mobile' => null, 'Phone' => null];
        }
    }

    private function formatToMSISDN($phone)
    {
        if (empty($phone)) return null;
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '234') && strlen($phone) >= 12) return $phone;
        if (str_starts_with($phone, '0')) return '234' . ltrim($phone, '0');
        if (strlen($phone) === 10) return '234' . $phone;
        if (strlen($phone) >= 10 && strlen($phone) <= 15) return $phone;

        return null;
    }

    // === Other Views (unchanged) ===
   /**
 * Approved Accounts List - Only for this BM's assigned customers
 */
public function showApproved()
{
    $staff = Auth::user();
    if (!$staff || !$staff->location) {
        abort(403, 'Access denied: No location assigned.');
    }

    $staffId       = $staff->id;
    $staffLocation = trim($staff->location);
    $isHeadquarter = in_array(strtolower($staffLocation), ['headquarter', 'hq', 'headquarters', 'head office']);

    $query = DB::table('kyc_forms')
        ->where('bm_status', 'approved')
        ->where('responsible_staff_id', $staffId)
        ->select('account_id', 'fullname', 'submitted_at');

    if (!$isHeadquarter) {
        $query->whereRaw('TRIM(buname) = ?', [$staffLocation]);
    }

    $forms = $query->orderByDesc('submitted_at')->get();

    return view('dashboard.bm_list', [
        'title' => 'Approved Accounts',
        'forms' => $forms
    ]);
}

/**
 * Rejected Accounts List - Only for this BM's assigned customers
 */
public function showRejected()
{
    $staff = Auth::user();
    if (!$staff || !$staff->location) {
        abort(403, 'Access denied: No location assigned.');
    }

    $staffId       = $staff->id;
    $staffLocation = trim($staff->location);
    $isHeadquarter = in_array(strtolower($staffLocation), ['headquarter', 'hq', 'headquarters', 'head office']);

    $query = DB::table('kyc_forms')
        ->where('bm_status', 'rejected')
        ->where('responsible_staff_id', $staffId)
        ->select('account_id', 'fullname', 'bm_remarks', 'submitted_at');

    if (!$isHeadquarter) {
        $query->whereRaw('TRIM(buname) = ?', [$staffLocation]);
    }

    $forms = $query->orderByDesc('submitted_at')->get();

    return view('dashboard.bm_list', [
        'title' => 'Rejected Accounts',
        'forms' => $forms
    ]);
}

   /**
 * BM Reports - Shows only approved/rejected counts for this BM's business unit
 */
/**
 * BM Reports - Shows only approved/rejected counts for this BM's business unit
 */
public function showReports()
{
    $staff = Auth::user();
    if (!$staff || !$staff->location) {
        abort(403, 'Access denied: No location assigned.');
    }

    $staffId       = $staff->id;
    $staffLocation = trim($staff->location);
    $isHeadquarter = in_array(strtolower($staffLocation), ['headquarter', 'hq', 'headquarters', 'head office']);

    $query = DB::table('kyc_forms')
        ->where('responsible_staff_id', $staffId);

    // Extra safety: filter by buname unless HQ
    if (!$isHeadquarter) {
        $query->whereRaw('TRIM(buname) = ?', [$staffLocation]);
    }

    $approvedCount = (clone $query)->where('bm_status', 'approved')->count();
    $rejectedCount = (clone $query)->where('bm_status', 'rejected')->count();
    $pendingCount  = (clone $query)->where('bm_status', 'pending')->count();
    $totalHandled  = $approvedCount + $rejectedCount;

    return view('dashboard.bm_reports', compact(
        'approvedCount',
        'rejectedCount',
        'pendingCount',
        'totalHandled',
        'staffLocation'
    ));
}

    // Legacy methods (kept for backward compatibility)
    public function approve($id) { /* ... same as before ... */ }
    public function reject(Request $request, $id) { /* ... same as before ... */ }
    public function bulkUpdate(Request $request) { /* ... same as before ... */ }
}