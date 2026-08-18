<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Traits\LogsAudit;
class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('employee');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('sort') && $request->sort === 'last_login_at') {
            $direction = $request->input('direction', 'desc') === 'asc' ? 'asc' : 'desc';
            $query->orderByRaw("last_login_at IS NULL ASC")->orderBy('last_login_at', $direction);
        } else {
            $query->orderBy('name');
        }

        $users = $query->paginate(15)->withQueryString();

        return Inertia::render('Users/Index', [
            'users'  => $users,
            'filters'=> $request->only(['search', 'role', 'status']),
            'stats'  => [
                'total_users'  => User::count(),
                'admin_users'  => User::where('role', 'admin')->count(),
                'hr_users'     => User::where('role', 'hr')->count(),
                'active_users' => User::where('is_active', true)->count(),
            ],
        ]);
    }

    public function toggleActive(Request $request, User $user)
    {
        // Prevent admin from deactivating themselves
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        LogsAudit::logAction($status, 'user', "User {$user->name} {$status}");
        return back()->with('success', "User account {$status} successfully.");
    }

    public function updateRole(Request $request, User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot change your own role.');
        }

        $request->validate([
            'role' => 'required|in:admin,hr,employee',
        ]);

        $user->update(['role' => $request->role]);
        LogsAudit::logAction('update', 'user', "Changed {$user->name}'s role to {$request->role}");

        return back()->with('success', "User role updated to {$request->role} successfully.");
    }
}