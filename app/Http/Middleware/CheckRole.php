<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // Check if user has required role
        if (in_array($user->role, $roles)) {
            // Check if user is active
            if (!$user->isActive()) {
                Auth::logout();
                return redirect('/login')->with('error', 'Your account has been deactivated.');
            }

            return $next($request);
        }

        return response()->view('errors.403', [], 403);
    }
}
