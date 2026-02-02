<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use App\Models\AuditLog;

if (!function_exists('log_audit')) {
    /**
     * Logs audit trail into audit_logs table.
     *
     * @param string $action          (e.g. login, logout, created, updated, deleted)
     * @param Illuminate\Database\Eloquent\Model|null $model
     * @param array|null $oldData     (before change)
     * @param array|null $newData     (after change)
     * @param string|null $description
     */
    function log_audit(string $action, $model = null, $oldData = null, $newData = null, string $description = null)
    {
        try {
            AuditLog::create([
                'user_id'        => Auth::id(),
                'action'         => $action,
                'auditable_type' => $model ? get_class($model) : null,
                'auditable_id'   => $model?->id,
                'description'    => $description,
                'old_values'     => $oldData ? json_encode($oldData) : null,
                'new_values'     => $newData ? json_encode($newData) : null,
                'ip_address'     => Request::ip(),
                'user_agent'     => Request::header('User-Agent'),
            ]);
        } catch (\Throwable $e) {
            // Optional: log or silently fail to avoid breaking core logic
            logger()->error('Failed to write audit log: ' . $e->getMessage());
        }
    }
}
