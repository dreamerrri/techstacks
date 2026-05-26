<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\PasswordValidator;
use App\Services\JWTTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Show registration form
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle login attempt with enhanced security
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        // Attempt to authenticate with secure password validation
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Check if user is active
            if (!$user->isActive()) {
                Auth::logout();
                $request->session()->invalidate();
                return back()->with('error', 'Your account has been deactivated. Please contact administrator.');
            }

            // Update last login timestamp
            $user->update(['last_login_at' => now()]);

            // Regenerate session for security
            $request->session()->regenerate();

            // Log login activity (using Laravel logs)
            \Illuminate\Support\Facades\Log::info('User login', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);
            // Redirect based on role
            return $this->redirectByRole($user);
        }

        // Log failed login attempt
        \Illuminate\Support\Facades\Log::warning('Failed login attempt', [
            'email' => $credentials['email'],
            'ip' => $request->ip(),
        ]);
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    /**
     * Handle registration
     */
    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        // Validate password strength
        $passwordValidation = PasswordValidator::validate($validated['password']);
        
        if (!$passwordValidation['valid']) {
            return back()->withErrors(['password' => $passwordValidation['errors']]);
        }

        // Check if password is common
        if (PasswordValidator::isCommonPassword($validated['password'])) {
            return back()->withErrors(['password' => 'This password is too common. Please choose a stronger password.']);
        }

        // Create user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'employee', // Default role
            'is_active' => true,
        ]);

        \Illuminate\Support\Facades\Log::info('User registration', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        // Auto-login the user
        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/dashboard')->with('success', 'Registration successful! Welcome to HR Management System.');
    }

    /**
     * Handle login via API with JWT token
     */
    public function apiLogin(LoginRequest $request)
    {
        $credentials = $request->validated();

        // Find user by email
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            \Illuminate\Support\Facades\Log::warning('Failed API login attempt', [
                'email' => $credentials['email'],
                'ip' => request()->ip(),
            ]);
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Check if user is active
        if (!$user->isActive()) {
            \Illuminate\Support\Facades\Log::warning('Login attempt on deactivated account', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
            return response()->json(['error' => 'Account has been deactivated'], 403);
        }

        // Generate JWT token
        $token = JWTTokenService::generateToken($user);
        $refreshToken = JWTTokenService::createRefreshToken($user);

        // Update last login
        $user->update(['last_login_at' => now()]);

        \Illuminate\Support\Facades\Log::info('API login successful', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'refresh_token' => $refreshToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Refresh JWT token
     */
    public function refreshToken(Request $request)
    {
        try {
            $token = JWTTokenService::getTokenFromRequest($request);
            $newToken = JWTTokenService::refreshToken($token);

            return response()->json([
                'success' => true,
                'token' => $newToken,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }

    /**
     * Validate password strength (for frontend)
     */
    public function validatePassword(Request $request)
    {
        $password = $request->input('password');
        
        if (!$password) {
            return response()->json(['error' => 'Password is required'], 400);
        }

        $validation = PasswordValidator::validate($password);
        $isCommon = PasswordValidator::isCommonPassword($password);

        return response()->json([
            'valid' => $validation['valid'] && !$isCommon,
            'strength' => $validation['strength'],
            'level' => $validation['level'],
            'errors' => $validation['errors'],
            'is_common' => $isCommon,
        ]);
    }

    /**
     * Redirect user based on their role
     */
    private function redirectByRole($user)
    {
        return match ($user->role) {
            'admin' => redirect('/admin/dashboard'),
            'hr' => redirect('/hr/dashboard'),
            default => redirect('/dashboard'),
        };
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            \Illuminate\Support\Facades\Log::info('User logout', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Handle API logout
     */
    public function apiLogout(Request $request)
    {
        try {
            $token = JWTTokenService::getTokenFromRequest($request);
            $decoded = JWTTokenService::verifyToken($token);
            
            $user = User::find($decoded->user_id);
            if ($user) {
                \Illuminate\Support\Facades\Log::info('API logout', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }

    /**
     * Show user dashboard (generic)
     */
    public function dashboard()
    {
        return view('dashboard', ['user' => Auth::user()]);
    }

    /**
     * Show admin dashboard
     */
    public function adminDashboard()
    {
        return view('admin.dashboard', ['user' => Auth::user()]);
    }

    /**
     * Show HR dashboard
     */
    public function hrDashboard()
    {
        return view('hr.dashboard', ['user' => Auth::user()]);
    }
}
