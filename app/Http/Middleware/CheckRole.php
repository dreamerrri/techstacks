<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckRole Middleware
 *
 * Restricts route access to users with specific roles.
 *
 * Registration (in bootstrap/app.php for Laravel 11+, or Kernel.php for Laravel 10):
 *   'role' => \App\Http\Middleware\CheckRole::class
 *
 * Usage in routes:
 *   Route::middleware('role:admin')->group(...)
 *   Route::middleware('role:admin,hr')->group(...)  // multiple allowed roles
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Must be authenticated (should already be enforced by 'auth' middleware upstream)
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        // Must hold one of the required roles
        if (!in_array($user->role, $roles)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}