<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;
use Carbon\Carbon;

class JWTTokenService
{
    /**
     * Generate JWT token
     */
    public static function generateToken(User $user, int $expiresIn = 86400): string
    {
        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'name' => $user->name,
            'iat' => time(),
            'exp' => time() + $expiresIn, // 24 hours by default
        ];

        return JWT::encode(
            $payload,
            config('app.key'),
            'HS256'
        );
    }

    /**
     * Decode and verify JWT token
     */
    public static function verifyToken(string $token)
    {
        try {
            $decoded = JWT::decode(
                $token,
                new Key(config('app.key'), 'HS256')
            );

            return $decoded;
        } catch (Exception $e) {
            throw new Exception('Invalid token: ' . $e->getMessage());
        }
    }

    /**
     * Refresh JWT token
     */
    public static function refreshToken(string $oldToken, int $expiresIn = 86400): string
    {
        try {
            $decoded = self::verifyToken($oldToken);
            $user = User::find($decoded->user_id);

            if (!$user) {
                throw new Exception('User not found');
            }

            return self::generateToken($user, $expiresIn);
        } catch (Exception $e) {
            throw new Exception('Unable to refresh token: ' . $e->getMessage());
        }
    }

    /**
     * Get token from request
     */
    public static function getTokenFromRequest($request): ?string
    {
        $token = null;

        // Check Authorization header
        if ($request->header('Authorization')) {
            $token = str_replace('Bearer ', '', $request->header('Authorization'));
        }

        // Check cookie
        if (!$token && $request->cookie('jwt_token')) {
            $token = $request->cookie('jwt_token');
        }

        return $token;
    }

    /**
     * Create refresh token (stored in database)
     */
    public static function createRefreshToken(User $user): string
    {
        $refreshToken = \Illuminate\Support\Str::random(60);

        // Store refresh token in cache or database
        cache()->put('refresh_token_' . $user->id, $refreshToken, Carbon::now()->addDays(7));

        return $refreshToken;
    }

    /**
     * Validate refresh token
     */
    public static function validateRefreshToken(User $user, string $refreshToken): bool
    {
        $storedToken = cache()->get('refresh_token_' . $user->id);

        return $storedToken === $refreshToken;
    }
}
