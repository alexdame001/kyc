<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    public function dashboard()
    {
        $kycForms = DB::table('kyc_forms')
            ->where('billing_status', 'approved')
            ->where('audit_status', 'pending')
            ->orderBy('submitted_at', 'desc')
            ->get();

        return view('dashboard.audit', compact('kycForms'));
    }

    public function approve($id)
    {
        $user = auth()->user();

        DB::statement('EXEC sp_approve_audit ?, ?, ?', [$id, $user->full_name, $user->role]);

        return redirect()->route('audit.dashboard')->with('success', 'Form approved by Audit.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'required|string|max:255',
        ]);

        $user = auth()->user();

        DB::statement('EXEC sp_reject_audit ?, ?, ?, ?', [
            $id,
            $user->full_name,
            $user->role,
            $request->remarks,
        ]);

        return redirect()->route('audit.dashboard')->with('success', 'Form rejected by Audit.');
    }
}
