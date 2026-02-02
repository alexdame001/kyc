<?php

namespace App\Http\Controllers;

use App\Models\KycForm;
use Illuminate\Http\Request;

class KycApprovalController extends Controller
{
public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:approved,rejected',
        ]);

        $user = $request->user();
        $kycForm = KycForm::findOrFail($id);
        $action = $request->input('action');

        switch ($user->role) {
            case 'billing':
                $kycForm->billing_status = $action;
                break;
            case 'customer_care':
                $kycForm->customer_care_status = $action;
                break;
            case 'audit':
                $kycForm->audit_status = $action;
                break;
            case 'admin':
                $kycForm->admin_status = $action;
                break;
            default:
                return response()->json(['message' => 'Unauthorized'], 403);
        }

        $kycForm->save();

        return response()->json([
            'message' => "Form {$action} by {$user->role}",
            'form' => $kycForm,
        ]);
    }
}
