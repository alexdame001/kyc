<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
    use App\Models\AuditLog;


class AdminController extends Controller
{
    public function dashboard()
    {
        $kycForms = DB::table('kyc_forms')
            ->where('audit_status', 'approved')
            ->where('admin_status', 'pending')
            ->orderBy('submitted_at', 'desc')
            ->get();

        return view('dashboard.admin', compact('kycForms'));
    }



    // public function showAuditLogs()
    // {
    //     // Fetch audit logs with pagination, ordering by the newest first
    //     $logs = AuditLog::orderBy('created_at', 'desc')->paginate(20);

    //     return view('admin.audit_logs', [
    //         'logs' => $logs
    //     ]);
    // }

    public function showAuditLogs(Request $request)
    {
        // Get the account ID from the request query string
        $accountId = $request->input('account_id');
        
        $query = AuditLog::orderBy('created_at', 'desc');

        // Apply filter if account_id is present
        if ($accountId) {
            $query->where('user_id', $accountId);
        }

        // Fetch paginated logs
        $logs = $query->paginate(20);

        // Append the accountId filter to the pagination links
        $logs->appends(['account_id' => $accountId]);

        return view('admin.audit_logs', [
            'logs' => $logs,
            'accountId' => $accountId // Pass the filter back to the view
        ]);
    }


    public function approve($id)
    {
        $user = auth()->user();

        DB::statement('EXEC sp_approve_admin ?, ?, ?', [$id, $user->full_name, $user->role]);

        return redirect()->route('admin.dashboard')->with('success', 'Form approved by Admin.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'required|string|max:255',
        ]);

        $user = auth()->user();

        DB::statement('EXEC sp_reject_admin ?, ?, ?, ?', [
            $id,
            $user->full_name,
            $user->role,
            $request->remarks,
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Form rejected by Admin.');
    }
}
