<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class AdminViewController extends Controller
{
    public function dashboard()
    {
        // Call your internal API endpoint
        $response = Http::withToken(auth()->user()->api_token)
            ->get(route('api.admin.dashboard'));

        if ($response->successful()) {
            $data = $response->json();

            return view('admin.dashboard', [
                'kycStats' => $data['kyc_stats'],
                'recentSubmissions' => $data['recent_submissions'],
                'userCounts' => $data['user_counts'],
                'recentAuditLogs' => $data['recent_audit_logs'],
            ]);
        }

        abort(500, 'Unable to fetch dashboard data.');
    }
}
