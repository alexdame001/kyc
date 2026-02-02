<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller; // ✅ This is required!
use Illuminate\Http\Request;
use App\Models\KycForm;
use App\Models\User;
use Auth;


class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('ensureAdmin');
    }

    /**
     * GET /api/admin/dashboard
     * Show general KYC stats.
     */
   public function dashboard()
{
    // KYC stats
    $totalSubmissions = KycForm::count();
    $approved = KycForm::where('status', 'approved')->count();
    $pending = KycForm::where('status', 'pending')->count();
    $rejected = KycForm::where('status', 'rejected')->count();

    // Recent submissions
    $recentSubmissions = Kycform::latest()->take(5)->with('user')->get();

    // User count by role (if using roles)
    $userCounts =User::selectRaw("role, COUNT(*) as count")
        ->groupBy('role')
        ->pluck('count', 'role');

    // (Optional) Recent audit logs
    $recentAuditLogs = \App\Models\AuditLog::latest()->take(5)->with('user')->get();

    return response()->json([
        'kyc_stats' => [
            'total' => $totalSubmissions,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
        ],
        'recent_submissions' => $recentSubmissions,
        'user_counts' => $userCounts,
        'recent_audit_logs' => $recentAuditLogs,
    ]);
}

    /**
     * GET /api/admin/kyc-submissions
     * List all KYC submissions (with optional filters)
     */
    public function allSubmissions(Request $request)
    {
        $query = KycForm::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $submissions = $query->latest()->paginate(20);
        return response()->json($submissions);
    }

    /**
     * GET /api/admin/kyc-submissions/{id}
     * View a single submission
     */
    public function viewSubmission($id)
    {
        $submission = KycForm::with(['user'])->findOrFail($id);
        return response()->json($submission);
    }

    /**
     * PUT /api/admin/kyc-submissions/{id}/status
     * Approve, reject, or mark as pending
     */
    public function updateSubmissionStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,pending',
            'remarks' => 'nullable|string|max:1000'
        ]);

        $submission = KycForm::findOrFail($id);
        $oldStatus = $submission->status;

        $submission->status = $request->status;
        $submission->remarks = $request->remarks;
        $submission->reviewed_by = Auth::id();
        $submission->save();

        // Optional: log in AuditLog
        audit_log('admin', 'Updated KYC submission status', $submission, [
            'old_status' => $oldStatus,
            'new_status' => $request->status
        ]);

        return response()->json([
            'message' => 'Submission status updated successfully.',
            'data' => $submission
        ]);
    }

    /**
     * GET /api/admin/users
     * List all users in the system
     */
    public function users()
    {
        $users = User::with('roles')->paginate(20);
        return response()->json($users);
    }
}
