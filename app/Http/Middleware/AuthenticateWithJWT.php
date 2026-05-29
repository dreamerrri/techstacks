<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\JWTTokenService;
use App\Models\User;
use Exception;

class AuthenticateWithJWT
{
    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization');
        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return response()->json(['message' => 'Token not provided'], 401);
        }

        $token = substr($header, 7);
        try {
            $payload = JWTTokenService::verifyToken($token);
            $user = User::find($payload->user_id);
            if (!$user) {
                return response()->json(['message' => 'User not found'], 401);
            }
            // Set the user for the request
            $request->setUserResolver(fn () => $user);
        } catch (Exception $e) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        return $next($request);
    }
}
