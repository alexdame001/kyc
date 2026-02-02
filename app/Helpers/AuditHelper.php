<?php

use App\Models\AuditLog;

if (!function_exists('audit_log')) {
    function audit_log($actor_role, $action, $auditable, $extra = [])
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => get_class($auditable),
            'auditable_id' => $auditable->id,
            'actor_role' => $actor_role,
            'description' => json_encode($extra),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
