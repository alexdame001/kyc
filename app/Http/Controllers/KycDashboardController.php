<?php

namespace App\Http\Controllers;

use App\Models\KycForm;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class KycDashboardController extends Controller
{
    /**
     * Display KYC forms assigned to a specific role.
     */
    public function index(Request $request, string $role): JsonResponse
    {
        $validRoles = ['billing', 'customer_care', 'audit', 'admin'];

        if (!in_array($role, $validRoles)) {
            return response()->json(['error' => 'Invalid role'], 400);
        }

        // Admin can view all forms
        if ($role === 'admin') {
            $forms = KycForm::orderBy('created_at', 'desc')->get();
        } else {
            $column = $role . '_status';
            $forms = KycForm::where($column, '!=', 'accepted')
                            ->orWhereNull($column)
                            ->orderBy('created_at', 'desc')
                            ->get();
        }

        return response()->json([
            'message' => ucfirst($role) . ' dashboard data fetched successfully',
            'data' => $forms,
        ]);
    }

    /**
     * Update role-based status for a form.
     */
    public function updateStatus(Request $request, string $role, int $id): JsonResponse
    {
        $validRoles = ['billing', 'customer_care', 'audit', 'admin'];
        $validStatuses = ['pending', 'reviewing', 'accepted', 'rejected'];

        if (!in_array($role, $validRoles)) {
            return response()->json(['error' => 'Invalid role'], 400);
        }

        $request->validate([
            'status' => 'required|in:' . implode(',', $validStatuses),
        ]);

        $kyc = KycForm::find($id);
        if (!$kyc) {
            return response()->json(['error' => 'KYC form not found'], 404);
        }

        // Update only the relevant column
        $column = $role === 'admin' ? 'status' : $role . '_status';
        $kyc->$column = $request->input('status');
        $kyc->save();

        return response()->json([
            'message' => 'Status updated successfully for ' . $role,
            'data' => $kyc,
        ]);
    }
}
