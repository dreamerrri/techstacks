<?php

namespace App\Http\Controllers;

use App\Models\User;
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
     * Handle login attempt
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Attempt to authenticate
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Check if user is active
            if (!$user->isActive()) {
                Auth::logout();
                return back()->with('error', 'Your account has been deactivated. Please contact administrator.');
            }

            // Update last login
            $user->update(['last_login_at' => now()]);

            $request->session()->regenerate();

            // Redirect based on role
            return $this->redirectByRole($user);
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
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
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'You have been logged out successfully.');
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
