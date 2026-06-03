<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckPermission Middleware
 *
 * Restricts route access to users with specific permissions.
 *
 * Registration (in bootstrap/app.php):
 *   'permission' => \App\Http\Middleware\CheckPermission::class
 *
 * Usage in routes:
 *   Route::middleware('permission:create.employees')->group(...)
 *   Route::middleware('permission:create.employees,edit.employees')->group(...)  // multiple allowed permissions
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        // Must be authenticated (should already be enforced by 'auth' middleware upstream)
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        // Must hold one of the required permissions
        if (!$user->hasAnyPermission($permissions)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
