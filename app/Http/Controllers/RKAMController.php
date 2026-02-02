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

class RKAMController extends Controller
{
    private $smsService;

    public function __construct(SmartSMSService $smsService = null)
    {
        $this->smsService = $smsService;
    }

    /**
     * Dashboard index
     */
 public function index(Request $request)
{
    $staff = Auth::user();
    if (!$staff || !$staff->location) {
        abort(403, 'Access denied: No location assigned.');
    }

    $staffLocation = trim(preg_replace('/^.*,\s*(.*)$/i', '$1', $staff->location));
    $staffStateLower = strtolower($staffLocation);

    $page = (int) $request->get('page', 1);
    $pageSize = (int) $request->get('pageSize', 20);
    $search = $request->get('search', '');

    try {
        // Call the stored procedure
        $totalCountParam = 0;
        $results = DB::connection('sqlsrv')->select(
            "EXEC sp_get_rkam_dashboard @PageNumber = ?, @PageSize = ?, @TotalCount = ? OUTPUT",
            [$page, $pageSize, &$totalCountParam] // Pass by reference for OUTPUT
        );
    } catch (\Exception $e) {
        Log::error('RKAM Proc Error: ' . $e->getMessage());
        abort(500, 'Dashboard query failed—check logs.');
    }

    $kycForms = collect($results);

    // Location filter (skip HQ)
    if ($staffStateLower !== 'headquarter') {
        $kycForms = $kycForms->filter(function ($form) use ($staffStateLower) {
            $stateMatch = isset($form->state) && str_contains(strtolower($form->state), $staffStateLower);
            $addressMatch = isset($form->new_address) && str_contains(strtolower($form->new_address), $staffStateLower);
            return $stateMatch || $addressMatch;
        });
    }

    // Search filter
    if ($search) {
        $kycForms = $kycForms->filter(fn($f) => str_contains(strtolower($f->account_id), strtolower($search)));
    }

    // Normalize old values
    foreach ($kycForms as $form) {
        $form->old_fullname = $form->old_fullname ?? '-';
        $form->old_address = $form->old_address ?? '-';
        $form->old_email = $form->old_email ?? '-';
        $form->old_phone = $form->old_phone ?? '-';
    }

    return view('dashboard.rkam', [
        'kycForms' => $kycForms,
        'totalCount' => $totalCountParam, // SP total count
        'currentPage' => $page,
        'pageSize' => $pageSize,
        'staffLocation' => $staff->location,
        'staffState' => $staffLocation,
        'search' => $search
    ]);
}


    /**
     * AJAX single update
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $status = $request->input('status');
        $notes = $request->input('notes', null);

        $form = DB::table('kyc_forms')->where('id', $id)->first();
        if (!$form) return response()->json(['success' => false, 'message' => 'Form not found'], 404);
        if (($form->rkam_status ?? 'pending') !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Form is not pending'], 409);
        }

        $staff = Auth::user();
        $staffId = Auth::id();
        $staffStateLower = strtolower(trim(preg_replace('/^.*,\s*(.*)$/i', '$1', $staff->location)));
        $isHeadquarter = str_contains($staffStateLower, 'headquarter');
        $stateMatch = $isHeadquarter || (isset($form->state) && str_contains(strtolower($form->state), $staffStateLower));

        try {
            DB::table('kyc_forms')->where('id', $id)->update([
                'rkam_status' => $status,
                'rkam_remarks' => $status === 'rejected' ? $notes : null,
                'rkam_reviewed_at' => Carbon::now(),
                'reviewed_by' => $staffId,
                'updated_at' => Carbon::now(),
            ]);

            try {
                $this->notifyOnStatusChange($form, $status, 'RKAM', $status === 'rejected' ? $notes : null);
            } catch (Exception $e) {
                Log::warning("Notification failed for RKAM update id={$id}: " . $e->getMessage());
            }

            Log::info('RKAM Status Update', ['id' => $id, 'status' => $status, 'staff_id' => $staffId]);
            return response()->json(['success' => true, 'message' => 'Updated!', 'id' => $id, 'status' => $status], 200);
        } catch (Exception $e) {
            Log::error('RKAM updateStatus error: ' . $e->getMessage(), ['id' => $id]);
            return response()->json(['success' => false, 'message' => 'Update failed'], 500);
        }
    }

    /**
     * Bulk Approve
     */
    public function bulkApprove(Request $request)
    {
        $validator = Validator::make($request->all(), ['ids' => 'required|array|min:1', 'ids.*' => 'integer|min:1']);
        if ($validator->fails()) return response()->json(['success' => false, 'message' => 'Validation failed'], 422);

        $ids = $request->input('ids', []);
        $staffId = Auth::id();
        $staffStateLower = strtolower(trim(preg_replace('/^.*,\s*(.*)$/i', '$1', Auth::user()->location)));
        $isHeadquarter = str_contains($staffStateLower, 'headquarter');

        $updatedCount = 0;
        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $form = DB::table('kyc_forms')->where('id', $id)->first();
                if (!$form || ($form->rkam_status ?? 'pending') !== 'pending') continue;
                $stateMatch = $isHeadquarter || (isset($form->state) && str_contains(strtolower($form->state), $staffStateLower));
                if (!$stateMatch) continue;

                DB::table('kyc_forms')->where('id', $id)->update([
                    'rkam_status' => 'approved',
                    'rkam_reviewed_at' => Carbon::now(),
                    'reviewed_by' => $staffId,
                    'updated_at' => Carbon::now(),
                ]);

                try { $this->notifyOnStatusChange($form, 'approved', 'RKAM'); } catch (Exception $e) { Log::warning($e->getMessage()); }
                $updatedCount++;
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => "{$updatedCount} forms approved.", 'approved' => $updatedCount], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Bulk approve failed.'], 500);
        }
    }

    /**
     * Bulk Reject
     */
    public function bulkReject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1', 'ids.*' => 'integer|min:1',
            'reason' => 'required|string|max:500',
        ]);
        if ($validator->fails()) return response()->json(['success' => false, 'message' => 'Validation failed'], 422);

        $ids = $request->input('ids', []);
        $reason = $request->input('reason');
        $staffId = Auth::id();
        $staffStateLower = strtolower(trim(preg_replace('/^.*,\s*(.*)$/i', '$1', Auth::user()->location)));
        $isHeadquarter = str_contains($staffStateLower, 'headquarter');

        $updatedCount = 0;
        DB::beginTransaction();
        try {
            foreach ($ids as $id) {
                $form = DB::table('kyc_forms')->where('id', $id)->first();
                if (!$form || ($form->rkam_status ?? 'pending') !== 'pending') continue;
                $stateMatch = $isHeadquarter || (isset($form->state) && str_contains(strtolower($form->state), $staffStateLower));
                if (!$stateMatch) continue;

                DB::table('kyc_forms')->where('id', $id)->update([
                    'rkam_status' => 'rejected',
                    'rkam_remarks' => $reason,
                    'rkam_reviewed_at' => Carbon::now(),
                    'reviewed_by' => $staffId,
                    'updated_at' => Carbon::now(),
                ]);

                try { $this->notifyOnStatusChange($form, 'rejected', 'RKAM', $reason); } catch (Exception $e) { Log::warning($e->getMessage()); }
                $updatedCount++;
            }
            DB::commit();
            return response()->json(['success' => true, 'message' => "{$updatedCount} forms rejected.", 'rejected' => $updatedCount], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Bulk reject failed.'], 500);
        }
    }

    /**
     * Notify on status change (SMS / RICO email)
     */
    private function notifyOnStatusChange($form, $status, $tier, $notes = null)
    {
        // similar logic as BMController but for RKAM
        $phone = $form->new_phone ?? $form->old_phone ?? null;
        if ($phone) {
            $phone = preg_replace('/\D/', '', $phone);
            if (str_starts_with($phone, '0')) $phone = '234' . ltrim($phone, '0');
            $smsService = $this->smsService ?? app(SmartSMSService::class);
            $msg = $status === 'approved' ? "KYC update approved!" : "KYC update rejected. Reason: " . ($notes ?? 'Review and resubmit.');
            $smsService->send($phone, $msg);
        }
        // RICO notification logic omitted for brevity
    }

    public function showReports()
    {
        $approvedCount = DB::table('kyc_forms')->where('rkam_status', 'approved')->count();
        $rejectedCount = DB::table('kyc_forms')->where('rkam_status', 'rejected')->count();
        return view('dashboard.rkam_reports', compact('approvedCount', 'rejectedCount'));
    }
    
}
