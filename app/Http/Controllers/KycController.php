<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KycController extends Controller
{
    public function submit(Request $request)
    {
        DB::statement("EXEC submit_kyc ?, ?, ?, ?, ?, ?, ?, ?", [
            $request->account_no,
            $request->meter_no,
            $request->customer_name,
            $request->customer_address,
            $request->phone_number,
            $request->email,
            $request->document_path,
            $request->submitted_by
        ]);

        // Log action
        DB::table('audit_logs')->insert([
            'user_id' => $request->user_id ?? null, // Attach user if available
            'action' => 'submit_kyc',
            'target_table' => 'kyc_forms',
            'target_id' => null, // You can fetch the latest inserted ID if needed
            'details' => json_encode($request->all())
        ]);

        return response()->json(['message' => 'KYC submitted successfully']);
    }

    public function getByRole(Request $request)
    {
        $records = DB::select("EXEC get_kyc_by_role ?", [$request->role]);

        // Log view action
        DB::table('audit_logs')->insert([
            'user_id' => $request->user_id ?? null,
            'action' => 'view_pending_kyc',
            'target_table' => 'kyc_forms',
            'target_id' => null,
            'details' => json_encode(['role' => $request->role])
        ]);

        return response()->json($records);
    }

    public function approve(Request $request)
    {
        DB::statement("EXEC approve_kyc ?, ?, ?", [
            $request->kyc_id,
            $request->role,
            $request->status
        ]);

        // Log approval
        DB::table('audit_logs')->insert([
            'user_id' => $request->user_id ?? null,
            'action' => 'approve_kyc',
            'target_table' => 'kyc_forms',
            'target_id' => $request->kyc_id,
            'details' => json_encode([
                'status' => $request->status,
                'role' => $request->role
            ])
        ]);

        return response()->json(['message' => 'Approval status updated']);
    }
}
