<?php

namespace App\Traits;

use App\Models\AuditLog;

trait LogsAudit
{
    public static function logAction(
        string $action,
        string $module,
        string $description,
        array $oldValues = null,
        array $newValues = null
    ) {
        AuditLog::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'old_values'  => $oldValues ? json_encode($oldValues) : null,
            'new_values'  => $newValues ? json_encode($newValues) : null,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ]);
    }
}