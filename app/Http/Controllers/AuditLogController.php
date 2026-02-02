<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuditLogController extends Controller
{
    public function __construct()
    {
        // Require authentication and the audit.view permission
        $this->middleware('auth:api');
        $this->middleware('permission:audit.view');
    }

    /**
     * GET /api/audit-logs
     * Optional filters: user_id, action_type, model, date_from, date_to
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }
        if ($request->filled('model')) {
            $query->where('auditable_type', $request->model);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return response()->json($logs, Response::HTTP_OK);
    }
}
