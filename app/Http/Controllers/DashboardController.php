<?php

namespace App\Http\Controllers;

use App\Models\KycForm;
use Illuminate\Http\Request;



class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->user()->role;

        switch ($role) {
            case 'billing':
                $forms = KycForm::where('billing_status', 'reviewing')->get();
                break;
            case 'customer_care':
                $forms = KycForm::where('customer_care_status', 'reviewing')->get();
                break;
            case 'audit':
                $forms = KycForm::where('audit_status', 'reviewing')->get();
                break;
            case 'admin':
                $forms = KycForm::where('admin_status', 'reviewing')->get();
                break;
            default:
                return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'role' => $role,
            'forms' => $forms,
        ]);
    }



    // // List KYC forms for a specific role
    // public function index(Request $request)
    // {
    //     $role = $request->user()->role;

    //     switch ($role) {
    //         case 'billing':
    //             $kycs = KycForm::where('billing_status', 'pending')->get();
    //             break;

    //         case 'customer_care':
    //             $kycs = KycForm::where('billing_status', 'accepted')
    //                            ->where('customer_care_status', 'pending')
    //                            ->get();
    //             break;

    //         case 'audit':
    //             $kycs = KycForm::where('billing_status', 'accepted')
    //                            ->where('customer_care_status', 'accepted')
    //                            ->where('audit_status', 'pending')
    //                            ->get();
    //             break;

    //         case 'admin':
    //             $kycs = KycForm::where('billing_status', 'accepted')
    //                            ->where('customer_care_status', 'accepted')
    //                            ->where('audit_status', 'accepted')
    //                            ->where('admin_status', 'pending')
    //                            ->get();
    //             break;

    //         default:
    //             return response()->json(['error' => 'Unauthorized role'], 403);
    //     }

    //     return response()->json([
    //         'message' => 'KYC forms fetched for ' . $role . ' dashboard',
    //         'data' => $kycs
    //     ]);
    // }

    // // Approve or reject a KYC form by a specific role
    // public function approve(Request $request, $id)
    // {
    //     $request->validate([
    //         'status' => 'required|in:accepted,rejected',
    //     ]);

    //     $kyc = KycForm::findOrFail($id);
    //     $role = $request->user()->role;
    //     $status = $request->input('status');

    //     switch ($role) {
    //         case 'billing':
    //             $kyc->billing_status = $status;
    //             break;

    //         case 'customer_care':
    //             if ($kyc->billing_status !== 'accepted') {
    //                 return response()->json(['error' => 'Billing must approve first'], 403);
    //             }
    //             $kyc->customer_care_status = $status;
    //             break;

    //         case 'audit':
    //             if ($kyc->billing_status !== 'accepted' || $kyc->customer_care_status !== 'accepted') {
    //                 return response()->json(['error' => 'Billing and Customer Care must approve first'], 403);
    //             }
    //             $kyc->audit_status = $status;
    //             break;

    //         case 'admin':
    //             if (
    //                 $kyc->billing_status !== 'accepted' ||
    //                 $kyc->customer_care_status !== 'accepted' ||
    //                 $kyc->audit_status !== 'accepted'
    //             ) {
    //                 return response()->json(['error' => 'All departments must approve before Admin'], 403);
    //             }
    //             $kyc->admin_status = $status;

    //             // Set final KYC status if admin accepts
    //             $kyc->status = $status === 'accepted' ? 'accepted' : 'rejected';
    //             break;

    //         default:
    //             return response()->json(['error' => 'Unauthorized role'], 403);
    //     }

    //     $kyc->save();

    //     return response()->json([
    //         'message' => 'KYC ' . $status . ' by ' . $role,
    //         'data' => $kyc
    //     ]);
    // }
}
