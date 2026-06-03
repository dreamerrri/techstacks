<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * LogAudit Middleware
 *
 * Logs user actions for audit trail.
 *
 * Registration (in bootstrap/app.php):
 *   'log.audit' => \App\Http\Middleware\LogAudit::class
 *
 * Usage in routes:
 *   Route::middleware('log.audit:module,action')->group(...)
 */
class LogAudit
{
    public function handle(Request $request, Closure $next, string $module, string $action): Response
    {
        $response = $next($request);

        if ($request->user()) {
            $oldValues = null;
            $newValues = null;

            // Capture changes for update operations
            if ($request->isMethod('PUT') || $request->isMethod('PATCH')) {
                $oldValues = $request->route()->parameter;
                $newValues = $request->all();
            }

            // Capture new data for create operations
            if ($request->isMethod('POST')) {
                $newValues = $request->all();
            }

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => $action,
                'module' => $module,
                'description' => "{$action} on {$module}",
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $response;
    }
}
