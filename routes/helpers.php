<?php

use App\Models\AuditLog;

if (! function_exists('log_audit')) {
    function log_audit($action, $model = null, $oldData = null, $newData = null, $description = null)
    {
        AuditLog::create([
            'user_id'        => auth()->id(),
            'action'         => $action,
            'auditable_type' => $model ? get_class($model) : null,
            'auditable_id'   => $model?->id,
            'description'    => $description,
            'old_values'     => $oldData ? json_encode($oldData) : null,
            'new_values'     => $newData ? json_encode($newData) : null,
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->header('User-Agent'),
        ]);
    }
}
