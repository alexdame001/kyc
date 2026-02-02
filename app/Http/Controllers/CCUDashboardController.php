<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KycForm;
use Carbon\Carbon;

class CCUDashboardController extends Controller
{
    public function index(Request $request)
    {
        $query = KycForm::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('account_type')) {
            $query->where('account_type', $request->account_type);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->from_date)->startOfDay(),
                Carbon::parse($request->to_date)->endOfDay()
            ]);
        }

        $kycForms = $query->latest()->paginate(20);

        return view('ccu.dashboard', compact('kycForms'));
    }

    public function agingAnalysis()
    {
        $allRequests = KycForm::all();
        $now = Carbon::now();

        $buckets = [
            '0-2 days' => 0,
            '3-5 days' => 0,
            '6-10 days' => 0,
            '>10 days' => 0
        ];

        foreach ($allRequests as $request) {
            $days = $now->diffInDays(Carbon::parse($request->created_at));

            if ($days <= 2) {
                $buckets['0-2 days']++;
            } elseif ($days <= 5) {
                $buckets['3-5 days']++;
            } elseif ($days <= 10) {
                $buckets['6-10 days']++;
            } else {
                $buckets['>10 days']++;
            }
        }

        return view('ccu.aging', compact('buckets'));
    }

    public function show($id)
    {
        $kyc = KycForm::findOrFail($id);
        return view('ccu.show', compact('kyc'));
    }
}
