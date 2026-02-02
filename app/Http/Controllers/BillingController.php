<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Helpers\AuditLogger;
use Carbon\Carbon;

class BillingController extends Controller
{
    /**
     * Extract staff state from user location
     * Examples:
     * - "Ibadan, OYO"        => OYO
     * - "Abeokuta, OGUN"     => OGUN
     * - "HEADQUARTER"        => HEADQUARTER (HQ user)
     */
    private function getStaffState()
    {
        $staff = Auth::user();

        if (!$staff || !$staff->location) {
            abort(403, 'Access denied: No location assigned.');
        }

        $loc = trim($staff->location);

        // If exactly HEADQUARTER → HQ user
        if (strtoupper($loc) === 'HEADQUARTER') {
            return 'HEADQUARTER';
        }

        // Split comma format (e.g., "Ibadan, OYO")
        $parts = explode(',', $loc);
        return strtoupper(trim(end($parts)));
    }


    /**
     * Dashboard — (Pagination + Search + State Filter)
     */
public function dashboard(Request $request)
    {
        $staff = Auth::user();
        if (!$staff || !$staff->location) {
            abort(403,'Access denied: No location assigned.');
        }

        $staffLocation = trim($staff->location);
        $isHeadquarter = in_array(strtolower($staffLocation), 
            ['headquarter','hq','headquarters','head office']);

        $page     = (int) $request->get('page', 1);
        $pageSize = (int) $request->get('pageSize', 50);

        $procCall = '
            DECLARE @TotalCount INT;
            EXEC sp_get_billing_dashboard ?, ?, @TotalCount OUTPUT;
            SELECT @TotalCount AS TotalCount;
        ';

        try {
            $results = DB::connection('sqlsrv')->select($procCall, [
                $page,
                $pageSize
            ]);
        } catch (\Exception $e) {
            Log::error('Billing Dashboard SP Error: '.$e->getMessage());
            abort(500,'Failed to load billing dashboard.');
        }

        $raw = collect($results);

        // Extract TotalCount like RICO
        $totalCount = 0;
        $index = $raw->search(fn($r) => property_exists($r,'TotalCount') || property_exists($r,'totalcount'));

        if ($index !== false) {
            $totalRow = $raw->pull($index);
            $totalCount = (int) ($totalRow->TotalCount ?? $totalRow->totalcount ?? 0);
        }

        if ($totalCount === 0 && $raw->isNotEmpty()) {
            $totalCount = $raw->count();
        }

        // Filter BU unless HQ
        $kycForms = $raw->filter(function($form) use ($staffLocation,$isHeadquarter){
            if($isHeadquarter) return true;
            return strcasecmp(trim($form->buname ?? ''), $staffLocation) === 0;
        });

        // Normalize old fields
        $kycForms->transform(function($f){
            $f->old_fullname = $f->old_fullname ?? '-';
            $f->old_address  = $f->old_address ?? '-';
            $f->old_email    = $f->old_email ?? '-';
            $f->old_phone    = $f->old_phone ?? '-';
            return $f;
        });

        return view('dashboard.billing',[
            'kycForms'      => $kycForms->values(),
            'totalCount'    => $totalCount,
            'currentPage'   => $page,
            'pageSize'      => $pageSize,
            'staffState'    => $staffLocation,
            'staffLocation'=> $staffLocation
        ]);
    }


 public function updateStatus(Request $request,$id)
    {
        $request->validate([
            'status'=>'required|in:approved,rejected',
            'notes'=>'nullable|string|max:500'
        ]);

        DB::table('kyc_forms')->where('id',$id)->update([
            'billing_status'      => $request->status,
            'billing_remarks'     => $request->status=='rejected' ? $request->notes : null,
            'billing_reviewed_at' => now(),
            'updated_at'          => now()
        ]);

        return response()->json(['success'=>true]);
    }

    // === BULK ===
    // public function bulkApprove(Request $r){ return $this->bulkAction($r,'approved'); }
    // public function bulkReject(Request $r){ return $this->bulkAction($r,'rejected',true); }

    private function bulkAction(Request $request,$status,$needReason=false)
    {
        $request->validate([
            'ids'=>'required|array|min:1',
            'reason'=>$needReason ? 'required|string|max:500' : 'nullable'
        ]);

        foreach($request->ids as $id){
            DB::table('kyc_forms')->where('id',$id)->update([
                'billing_status'=>$status,
                'billing_remarks'=>$status=='rejected' ? $request->reason : null,
                'billing_reviewed_at'=>now(),
                'updated_at'=>now()
            ]);
        }

        return response()->json(['success'=>true]);
    }


    /**
     * Check if staff can review a specific form
     */
    private function canReview($form)
    {
        $staffState = $this->getStaffState();

        if ($staffState === 'HEADQUARTER') {
            return true; // HQ can review anything
        }

        return strtoupper($form->state) === $staffState;
    }


    /**
     * Single Approve
     */
    public function approve($id)
    {
        $form = DB::table('kyc_forms')->where('id', $id)->first();
        if (!$form) return back()->with('error', 'Record not found.');

        if (!$this->canReview($form)) {
            return back()->with('error', 'You cannot approve a record outside your state.');
        }

        DB::table('kyc_forms')->where('id', $id)->update([
            'billing_status' => 'approved',
            'updated_at'     => now(),
        ]);

        AuditLogger::log('Billing approved', $id, "Approved KYC for {$form->account_id}");

        return back()->with('success', 'Form approved.');
    }



    /**
     * Single Reject
     */
    public function reject(Request $request, $id)
    {
        $request->validate(['remarks' => 'required|string|max:255']);

        $form = DB::table('kyc_forms')->where('id', $id)->first();
        if (!$form) return back()->with('error', 'Record not found.');

        if (!$this->canReview($form)) {
            return back()->with('error', 'You cannot reject a record outside your state.');
        }

        DB::table('kyc_forms')->where('id', $id)->update([
            'billing_status' => 'rejected',
            'billing_remarks'=> $request->remarks,
            'updated_at'     => now(),
        ]);

        AuditLogger::log('Billing rejected', $id, "Rejected KYC for {$form->account_id}");

        return back()->with('success', 'Form rejected.');
    }



    /**
     * Bulk Approve
     */
    public function bulkApprove(Request $request)
    {
        $ids = $request->ids ?? [];
        if (empty($ids)) return response()->json(['message' => 'No record selected'], 400);

        $staffState = $this->getStaffState();
        $isHQ = $staffState === 'HEADQUARTER';

        $allowed = [];

        foreach ($ids as $id) {
            $form = DB::table('kyc_forms')->where('id', $id)->first();

            if ($form && ($isHQ || strtoupper($form->state) === $staffState)) {
                $allowed[] = $id;
            }
        }

        if (empty($allowed)) {
            return response()->json(['message' => 'No permitted records'], 403);
        }

        DB::table('kyc_forms')->whereIn('id', $allowed)->update([
            'billing_status' => 'approved',
            'updated_at'     => now(),
        ]);

        AuditLogger::log('Bulk billing approve', 0, "Approved IDs: " . implode(',', $allowed));

        return response()->json(['success' => true, 'count' => count($allowed)]);
    }



    /**
     * Bulk Reject (Now requires remarks)
     */
    public function bulkReject(Request $request)
    {
        $ids = $request->ids ?? [];
        $remark = $request->remarks ?? null;

        if (empty($ids)) return response()->json(['message' => 'No record selected'], 400);
        if (!$remark) return response()->json(['message' => 'Remarks required'], 422);

        $staffState = $this->getStaffState();
        $isHQ = $staffState === 'HEADQUARTER';

        $allowed = [];

        foreach ($ids as $id) {
            $form = DB::table('kyc_forms')->where('id', $id)->first();

            if ($form && ($isHQ || strtoupper($form->state) === $staffState)) {
                $allowed[] = $id;
            }
        }

        if (empty($allowed)) {
            return response()->json(['message' => 'No permitted records'], 403);
        }

        DB::table('kyc_forms')->whereIn('id', $allowed)->update([
            'billing_status'  => 'rejected',
            'billing_remarks' => $remark,
            'updated_at'      => now(),
        ]);

        AuditLogger::log('Bulk billing reject', 0, "Rejected IDs: " . implode(',', $allowed));

        return response()->json(['success' => true, 'count' => count($allowed)]);
    }



    /**
     * Reports
     */
    public function showReports()
    {
        return view('dashboard.billing_reports', [
            'approvedCount' => DB::table('kyc_forms')->where('billing_status', 'approved')->count(),
            'rejectedCount' => DB::table('kyc_forms')->where('billing_status', 'rejected')->count(),
        ]);
    }
}
