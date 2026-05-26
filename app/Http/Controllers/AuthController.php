<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
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
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle login — all roles land on the same /dashboard route.
     * The 'auth' middleware on that route is the security gate.
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (!$user->isActive()) {
                Auth::logout();
                $request->session()->invalidate();
                return back()->with('error', 'Your account has been deactivated. Please contact administrator.');
            }

            $user->update(['last_login_at' => now()]);
            $request->session()->regenerate();

            \Illuminate\Support\Facades\Log::info('User login', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'ip'      => $request->ip(),
            ]);

            return redirect()->route('dashboard');
        }

        \Illuminate\Support\Facades\Log::warning('Failed login attempt', [
            'email' => $credentials['email'],
            'ip'    => $request->ip(),
        ]);

        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $passwordValidation = PasswordValidator::validate($validated['password']);
        if (!$passwordValidation['valid']) {
            return back()->withErrors(['password' => $passwordValidation['errors']]);
        }

        if (PasswordValidator::isCommonPassword($validated['password'])) {
            return back()->withErrors(['password' => 'This password is too common. Please choose a stronger password.']);
        }

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role'      => 'employee', // default per DB enum
            'is_active' => true,
        ]);

        \Illuminate\Support\Facades\Log::info('User registration', [
            'user_id' => $user->id,
            'email'   => $user->email,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Registration successful! Welcome to HR Management System.');
    }

    /**
     * Dashboard — single route, role-scoped data.
     *
     * Security layers:
     *   1. 'auth' middleware on the route: blocks unauthenticated requests entirely.
     *   2. Role check here: each branch only queries and passes data
     *      that the role is permitted to see. An employee can never
     *      receive admin or HR data regardless of URL manipulation.
     *
     * The blade view uses the same $stats/$actions passed here,
     * so there is no role-sensitive logic leaking into the frontend.
     */
    public function dashboard()
    {
        $user = Auth::user();

        // --- Admin: sees user/system-level stats ---
        if ($user->role === 'admin') {
            $stats = [
                [
                    'label' => 'Total Users',
                    'value' => User::count(),
                    'icon'  => 'fa-users',
                    'color' => '#dc2626',
                ],
                [
                    'label' => 'Admin Users',
                    'value' => User::where('role', 'admin')->count(),
                    'icon'  => 'fa-user-shield',
                    'color' => '#991b1b',
                ],
                [
                    'label' => 'HR Personnel',
                    'value' => User::where('role', 'hr')->count(),
                    'icon'  => 'fa-user-tie',
                    'color' => '#fbbf24',
                ],
                [
                    'label' => 'Active Accounts',
                    'value' => User::where('is_active', true)->count(),
                    'icon'  => 'fa-check-circle',
                    'color' => '#10b981',
                ],
            ];

            $actions = [
                ['label' => 'Create User',   'icon' => 'fa-user-plus'],
                ['label' => 'Manage Roles',  'icon' => 'fa-user-tag'],
                ['label' => 'System Backup', 'icon' => 'fa-database'],
                ['label' => 'View Logs',     'icon' => 'fa-history'],
            ];
        }

        // --- HR: sees employee/HR-level stats ---
        elseif ($user->role === 'hr') {
            $stats = [
                [
                    'label' => 'Total Employees',
                    'value' => Employee::where('is_archived', false)->count(),
                    'icon'  => 'fa-users',
                    'color' => '#2563eb',
                ],
                [
                    'label' => 'Regular',
                    'value' => Employee::where('employment_status', 'Regular')->where('is_archived', false)->count(),
                    'icon'  => 'fa-calendar-check',
                    'color' => '#1e40af',
                ],
                [
                    'label' => 'Probationary',
                    'value' => Employee::where('employment_status', 'Probationary')->where('is_archived', false)->count(),
                    'icon'  => 'fa-clipboard-list',
                    'color' => '#fbbf24',
                ],
                [
                    'label' => 'Archived',
                    'value' => Employee::where('is_archived', true)->count(),
                    'icon'  => 'fa-archive',
                    'color' => '#6b7280',
                ],
            ];

            $actions = [
                ['label' => 'Add Employee',   'icon' => 'fa-user-plus'],
                ['label' => 'Payroll',        'icon' => 'fa-calculator'],
                ['label' => 'Leave Requests', 'icon' => 'fa-inbox'],
                ['label' => 'Reports',        'icon' => 'fa-file-pdf'],
            ];
        }

        // --- Employee: sees only their own context, no sensitive counts ---
        else {
            $stats = [
                [
                    'label' => 'Department',
                    // Find this user's employee record if it exists
                    'value' => Employee::where('email', $user->email)->value('department') ?? '—',
                    'icon'  => 'fa-building',
                    'color' => '#667eea',
                ],
                [
                    'label' => 'Position',
                    'value' => Employee::where('email', $user->email)->value('position') ?? '—',
                    'icon'  => 'fa-id-badge',
                    'color' => '#764ba2',
                ],
                [
                    'label' => 'Employment Status',
                    'value' => Employee::where('email', $user->email)->value('employment_status') ?? '—',
                    'icon'  => 'fa-briefcase',
                    'color' => '#fbbf24',
                ],
                [
                    'label' => 'Date Hired',
                    'value' => optional(Employee::where('email', $user->email)->value('date_hired'))->format('M d, Y') ?? '—',
                    'icon'  => 'fa-calendar',
                    'color' => '#10b981',
                ],
            ];

            $actions = [
                ['label' => 'My Profile',    'icon' => 'fa-user'],
                ['label' => 'Payslips',      'icon' => 'fa-file-invoice-dollar'],
                ['label' => 'Leave Request', 'icon' => 'fa-calendar-times'],
                ['label' => 'Attendance',    'icon' => 'fa-clock'],
            ];
        }

        return view('dashboard', compact('user', 'stats', 'actions'));
    }

    // -----------------------------------------------------------------------
    // API (JWT)
    // -----------------------------------------------------------------------

    public function apiLogin(LoginRequest $request)
    {
        $credentials = $request->validated();
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            \Illuminate\Support\Facades\Log::warning('Failed API login attempt', [
                'email' => $credentials['email'],
                'ip'    => request()->ip(),
            ]);
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        if (!$user->isActive()) {
            return response()->json(['error' => 'Account has been deactivated'], 403);
        }

        $token        = JWTTokenService::generateToken($user);
        $refreshToken = JWTTokenService::createRefreshToken($user);
        $user->update(['last_login_at' => now()]);

        return response()->json([
            'success'       => true,
            'message'       => 'Login successful',
            'token'         => $token,
            'refresh_token' => $refreshToken,
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    public function refreshToken(Request $request)
    {
        try {
            $token    = JWTTokenService::getTokenFromRequest($request);
            $newToken = JWTTokenService::refreshToken($token);
            return response()->json(['success' => true, 'token' => $newToken]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }

    public function validatePassword(Request $request)
    {
        $password = $request->input('password');
        if (!$password) {
            return response()->json(['error' => 'Password is required'], 400);
        }

        $validation = PasswordValidator::validate($password);
        $isCommon   = PasswordValidator::isCommonPassword($password);

        return response()->json([
            'valid'     => $validation['valid'] && !$isCommon,
            'strength'  => $validation['strength'],
            'level'     => $validation['level'],
            'errors'    => $validation['errors'],
            'is_common' => $isCommon,
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            \Illuminate\Support\Facades\Log::info('User logout', [
                'user_id' => $user->id,
                'email'   => $user->email,
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'You have been logged out successfully.');
    }

    public function apiLogout(Request $request)
    {
        try {
            $token   = JWTTokenService::getTokenFromRequest($request);
            $decoded = JWTTokenService::verifyToken($token);
            $user    = User::find($decoded->user_id);

            if ($user) {
                \Illuminate\Support\Facades\Log::info('API logout', [
                    'user_id' => $user->id,
                    'email'   => $user->email,
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }
    }
}