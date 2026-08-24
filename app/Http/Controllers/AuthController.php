<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\PasswordResetRequest;
use App\Services\PasswordValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Traits\LogsAudit;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;


class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function showRegister()
    {
        return Inertia::render('Auth/Register');
    }

    public function showReset()
    {
        return Inertia::render('Auth/Reset');
    }
  public function showUpdatePassword(Request $request)
{
    return Inertia::render('Auth/UpdatePassword', [
        'token' => $request->query('token'),
        'email' => $request->query('email'),
    ]);
}

public function updatePassword(Request $request)
{
    $request->validate([
        'token'    => 'required',
        'email'    => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->save();
        }
    );

    return $status === Password::PASSWORD_RESET
        ? redirect()->route('login')->with('success', __($status))
        : back()->withErrors(['email' => __($status)]);
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

        $user->updateQuietly(['last_login_at' => now()]);
            LogsAudit::logAction('login', 'auth', "User {$user->name} logged in");
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
            'role'      => 'employee',
            'is_active' => true,
        ]);
        LogsAudit::logAction('create', 'user', "New user registered: {$user->name}"); 

        // --- FIX: Auto-create a linked Employee record for every new registered user ---
        if (!Employee::where('email', $validated['email'])->exists()) {
            $nameParts = explode(' ', trim($validated['name']), 2);
            $firstName = $nameParts[0];
            $lastName  = $nameParts[1] ?? $nameParts[0];

            $employee = Employee::create([
                'user_id'           => $user->id,
                'first_name'        => $firstName,
                'last_name'         => $lastName,
                'email'             => $validated['email'],
                'birthdate'         => '1990-01-01',      // placeholder — HR should update
                'gender'            => 'Other',
                'civil_status'      => 'Single',
                'address'           => '',
                'contact_number'    => '',
                'department'        => 'Unassigned',
                'position'          => 'Unassigned',
                'employment_status' => 'Probationary',
                'date_hired'        => now()->toDateString(),
                'salary_type'       => 'Monthly',
                'basic_salary'      => 0,
            ]);
        } else {
            // Employee record already exists (e.g. HR added them first) — just link the user_id
            Employee::where('email', $validated['email'])
                ->whereNull('user_id')
                ->update(['user_id' => $user->id]);
        }
        // --- END FIX ---

        \Illuminate\Support\Facades\Log::info('User registration', [
            'user_id' => $user->id,
            'email'   => $user->email,
        ]);

       return redirect()->route('login')->with('success', 'Registration successful! Please log in.');
    }


  public function sendResetEmail(Request $request)
{
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink($request->only('email'));

    return $status === Password::RESET_LINK_SENT
        ? back()->with('success', __($status))
        : back()->withErrors(['email' => __($status)]);
}

    /**
     * Dashboard — single route, role-scoped data.
     */
public function dashboard()
{
    $user = Auth::user();

    // Only expose account statistics to roles whose UI actually renders them
    $counts = [];
    if ($user->isAdmin()) {
        $counts += [
            'total_users'    => User::count(),
            'admin_users'    => User::where('role', 'admin')->count(),
            'hr_users'       => User::where('role', 'hr')->count(),
            'active_users'   => User::where('is_active', true)->count(),
        ];
    }
    if ($user->isHR()) {
        $counts += [
            'total_employees'=> Employee::where('is_archived', false)->count(),
            'regular'        => Employee::where('employment_status', 'Regular')->where('is_archived', false)->count(),
            'probationary'   => Employee::where('employment_status', 'Probationary')->where('is_archived', false)->count(),
            'archived'       => Employee::where('is_archived', true)->count(),
        ];
    }

    return Inertia::render('Dashboard', ['counts' => $counts]);
}

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            \Illuminate\Support\Facades\Log::info('User logout', [
                'user_id' => $user->id,
                'email'   => $user->email,
            ]);
             LogsAudit::logAction('logout', 'auth', "User {$user->name} logged out"); 
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'You have been logged out successfully.');
    }
}
