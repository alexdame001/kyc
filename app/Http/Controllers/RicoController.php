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

class RicoController extends Controller
{
    private $smsService;

    public function __construct(SmartSMSService $smsService = null)
    {
        $this->smsService = $smsService;
    }

    /**
     * RICO Dashboard - Shows only pending forms where BM or RKAM approved
     */
    public function dashboard(Request $request)
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

// DECLARE the output variable and call SP correctly
$procCall = 'DECLARE @TotalCount INT;
             EXEC sp_get_rico_dashboard ?, ?, @TotalCount OUTPUT;
             SELECT @TotalCount AS TotalCount;';

try {
    $results = DB::connection('sqlsrv')->select($procCall, [$page, $pageSize]);
} catch (\Exception $e) {
    Log::error('RICO Dashboard SP Error: ' . $e->getMessage());
    abort(500, 'Failed to load dashboard data. Please try again later.');
}

// Collect results
$kycFormsRaw = collect($results);

// Extract total count
$totalCount = 0;
$totalRowIndex = $kycFormsRaw->search(function ($row) {
    return property_exists($row, 'TotalCount') || property_exists($row, 'totalcount');
});
if ($totalRowIndex !== false) {
    $totalRow = $kycFormsRaw->pull($totalRowIndex);
    $totalCount = (int) ($totalRow->TotalCount ?? $totalRow->totalcount ?? 0);
}
if ($totalCount === 0 && $kycFormsRaw->isNotEmpty()) {
    $totalCount = $kycFormsRaw->count();
}

        // Filter BU unless HQ
        $kycForms = $kycFormsRaw->filter(function ($form) use ($staffLocation, $isHeadquarter) {
            if ($isHeadquarter) return true;
            $formBUName = trim($form->buname ?? '');
            return strcasecmp($formBUName, $staffLocation) === 0;
        });

        // Normalize old values
        $kycForms->transform(function ($form) {
            $form->old_fullname = $form->old_fullname ?? '-';
            $form->old_address  = $form->old_address ?? '-';
            $form->old_email    = $form->old_email ?? '-';
            $form->old_phone    = $form->old_phone ?? '-';
            return $form;
        });

        return view('dashboard.rico', [
            'kycForms'      => $kycForms->values(),
            'totalCount'    => $totalCount,
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
            return response()->json(['success'=>false,'message'=>'Validation failed','errors'=>$validator->errors()],422);
        }

        $status = $request->input('status');
        $notes  = $request->input('notes');

        $staff = Auth::user();
        $staffId       = $staff->id;
        $staffLocation = trim($staff->location ?? '');
        $isHeadquarter = in_array(strtolower($staffLocation), ['headquarter','hq','headquarters','head office']);

        $form = DB::table('kyc_forms')->where('id',$id)->first();
        if (!$form) return response()->json(['success'=>false,'message'=>'Form not found'],404);
        if (($form->rico_status ?? 'pending') !== 'pending') return response()->json(['success'=>false,'message'=>'Form is not pending'],409);

        // Authorization checks
        if ($form->responsible_staff_id != $staffId) return response()->json(['success'=>false,'message'=>'Unauthorized: Not assigned'],403);
        if (!$isHeadquarter) {
            $formBUName = trim($form->buname ?? '');
            if (strcasecmp($formBUName,$staffLocation) !== 0) return response()->json(['success'=>false,'message'=>'Unauthorized: Not your BU'],403);
        }

        try {
            DB::table('kyc_forms')->where('id',$id)->update([
                'rico_status'       => $status,
                'rico_remarks'      => $status==='rejected' ? $notes : null,
                'rico_reviewed_at'  => Carbon::now(),
                'reviewed_by'       => $staffId,
                'updated_at'        => Carbon::now(),
            ]);

            try {
                $this->notifyOnStatusChange($form,$status,'RICO',$status==='rejected'? $notes : null);
            } catch (Exception $e) {
                Log::warning("Notification failed for form {$id}: ".$e->getMessage());
            }

            Log::info('RICO Status Update',['id'=>$id,'status'=>$status,'staff_id'=>$staffId]);

            return response()->json(['success'=>true,'message'=>'Status updated successfully!'],200);
        } catch (Exception $e) {
            Log::error('RICO updateStatus error: '.$e->getMessage(),['id'=>$id]);
            return response()->json(['success'=>false,'message'=>'Update failed'],500);
        }
    }

    /**
     * AJAX: Bulk Approve
     */
    public function bulkApprove(Request $request) { return $this->bulkAction($request,'approved'); }

    /**
     * AJAX: Bulk Reject
     */
    public function bulkReject(Request $request) { 
        $request->merge(['status'=>'rejected']); 
        return $this->bulkAction($request,'rejected',true); 
    }

    /**
     * Shared logic for bulk approve/reject
     */
    private function bulkAction(Request $request,$status,$requireReason=false)
    {
        $rules = ['ids'=>'required|array|min:1','ids.*'=>'integer|min:1'];
        if($requireReason) $rules['reason']='required|string|max:500';

        $validator = Validator::make($request->all(),$rules);
        if($validator->fails()) return response()->json(['success'=>false,'message'=>'Validation failed','errors'=>$validator->errors()],422);

        $ids = $request->input('ids',[]);
        $reason = $request->input('reason');
        $staff = Auth::user();
        $staffId = $staff->id;
        $staffLocation = trim($staff->location ?? '');
        $isHeadquarter = in_array(strtolower($staffLocation),['headquarter','hq','headquarters','head office']);

        $updatedCount = 0;
        DB::beginTransaction();

        try {
            foreach($ids as $id){
                $form = DB::table('kyc_forms')->where('id',$id)->first();
                if(!$form || ($form->rico_status ?? 'pending')!=='pending') continue;
                if($form->responsible_staff_id != $staffId) continue;
                if(!$isHeadquarter){
                    $formBUName = trim($form->buname ?? '');
                    if(strcasecmp($formBUName,$staffLocation)!==0) continue;
                }

                DB::table('kyc_forms')->where('id',$id)->update([
                    'rico_status'       => $status,
                    'rico_remarks'      => $status==='rejected' ? $reason : null,
                    'rico_reviewed_at'  => Carbon::now(),
                    'reviewed_by'       => $staffId,
                    'updated_at'        => Carbon::now(),
                ]);

                try {
                    $this->notifyOnStatusChange($form,$status,'RICO',$status==='rejected'? $reason : null);
                } catch (Exception $e) {
                    Log::warning("Notification failed (bulk) for form {$id}: ".$e->getMessage());
                }

                $updatedCount++;
            }

            DB::commit();

            Log::info("Bulk {$status} by RICO",['count'=>$updatedCount,'staff_id'=>$staffId]);

            return response()->json([
                'success'=>true,
                'message'=>"{$updatedCount} form(s) ".($status==='approved'?'approved':'rejected').'.',
                $status=>$updatedCount
            ],200);

        } catch(Exception $e){
            DB::rollBack();
            Log::error("Bulk {$status} failed: ".$e->getMessage(),['ids'=>$ids]);
            return response()->json(['success'=>false,'message'=>'Bulk action failed.'],500);
        }
    }

    // === Notification & Helper Methods ===
    private function notifyOnStatusChange($form,$status,$tier,$notes=null)
    {
        $accountType = $form->account_type ?? ($form->accountType ?? 'prepaid');
        $accountId   = $form->account_id ?? ($form->AccountNo ?? null);
        $customer = $this->getCustomerByAccountId($accountId,$accountType);
        $phoneRaw = $customer->Mobile ?? $customer->Phone ?? ($form->phone ?? null);
        $phone    = $this->formatToMSISDN($phoneRaw);

        if($phone){
            $smsService = $this->smsService ?? app(SmartSMSService::class);
            $msg = $status==='approved'
                ? "IBEDC KYC: Your update has been approved! Changes are now live."
                : "IBEDC KYC: Your update was rejected. Reason: ".($notes ?? 'Review and resubmit.')." Log in to resubmit.";

            try{
                $result = $smsService->send($phone,$msg);
                if(!($result['success'] ?? false)) Log::error("SMS failed for {$accountId}: ".($result['error']??'Unknown'));
            } catch(Exception $e){
                Log::warning("SMS exception for {$accountId}: ".$e->getMessage());
            }
        }

        // Notify RICO if fully approved
        if($status==='approved'){
            $rkamDone = DB::table('kyc_forms')->where('id',$form->id)->value('rkam_status')==='approved';
            if($rkamDone){
                try{
                    $ricoEmails = DB::table('users')->where('role','rico')->pluck('email')->toArray();
                    foreach($ricoEmails as $email){
                        Mail::to($email)->queue(new RkamKycAlert($form,'Ready for RICO review!'));
                    }
                } catch(Exception $e){
                    Log::warning("RICO email failed for form {$form->id}: ".$e->getMessage());
                }
            }
        }
    }

    private function getCustomerByAccountId($accountId,$type)
    {
        try{
            if(!$accountId) return (object)['Mobile'=>null,'Phone'=>null];
            if(strtolower($type)==='postpaid'){
                return DB::connection('sqlsrv89')->table('CustomerNew')->where('AccountNo',$accountId)->first() ?? (object)['Mobile'=>null,'Phone'=>null];
            }
            return DB::connection('sqlsrv81')->table('Customers')->where('MeterNo',$accountId)->first() ?? (object)['Mobile'=>null,'Phone'=>null];
        } catch(Exception $e){
            Log::error('Customer lookup error: '.$e->getMessage());
            return (object)['Mobile'=>null,'Phone'=>null];
        }
    }

    private function formatToMSISDN($phone)
    {
        if(empty($phone)) return null;
        $phone = preg_replace('/\D/','',$phone);
        if(str_starts_with($phone,'234') && strlen($phone)>=12) return $phone;
        if(str_starts_with($phone,'0')) return '234'.ltrim($phone,'0');
        if(strlen($phone)===10) return '234'.$phone;
        if(strlen($phone)>=10 && strlen($phone)<=15) return $phone;
        return null;
    }

    // === Views for Approved/Rejected/Reports ===
    public function showApproved()
    {
        $staff = Auth::user();
        if(!$staff || !$staff->location) abort(403,'Access denied: No location assigned.');
        $staffId = $staff->id;
        $staffLocation = trim($staff->location);
        $isHQ = in_array(strtolower($staffLocation),['headquarter','hq','headquarters','head office']);

        $query = DB::table('kyc_forms')->where('rico_status','approved')->where('responsible_staff_id',$staffId)->select('account_id','fullname','submitted_at');
        if(!$isHQ) $query->whereRaw('TRIM(buname)=?',[$staffLocation]);
        $forms = $query->orderByDesc('submitted_at')->get();

        return view('dashboard.rico_list',['title'=>'Approved Accounts','forms'=>$forms]);
    }

    public function showRejected()
    {
        $staff = Auth::user();
        if(!$staff || !$staff->location) abort(403,'Access denied: No location assigned.');
        $staffId = $staff->id;
        $staffLocation = trim($staff->location);
        $isHQ = in_array(strtolower($staffLocation),['headquarter','hq','headquarters','head office']);

        $query = DB::table('kyc_forms')->where('rico_status','rejected')->where('responsible_staff_id',$staffId)->select('account_id','fullname','rico_remarks','submitted_at');
        if(!$isHQ) $query->whereRaw('TRIM(buname)=?',[$staffLocation]);
        $forms = $query->orderByDesc('submitted_at')->get();

        return view('dashboard.rico_list',['title'=>'Rejected Accounts','forms'=>$forms]);
    }

    public function showReports()
    {
        $staff = Auth::user();
        if(!$staff || !$staff->location) abort(403,'Access denied: No location assigned.');
        $staffId = $staff->id;
        $staffLocation = trim($staff->location);
        $isHQ = in_array(strtolower($staffLocation),['headquarter','hq','headquarters','head office']);

        $query = DB::table('kyc_forms')->where('responsible_staff_id',$staffId);
        if(!$isHQ) $query->whereRaw('TRIM(buname)=?',[$staffLocation]);

        $approvedCount = (clone $query)->where('rico_status','approved')->count();
        $rejectedCount = (clone $query)->where('rico_status','rejected')->count();
        $pendingCount  = (clone $query)->where('rico_status','pending')->count();
        $totalHandled  = $approvedCount + $rejectedCount;

        return view('dashboard.rico_reports',compact('approvedCount','rejectedCount','pendingCount','totalHandled','staffLocation'));
    }
}
