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
        $query = User::query()->with('employee')
            // Pending (never-approved) registrations live in the approval queue only
            ->whereNotNull('approved_at');

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
            'pendingCount' => self::pendingQuery()->count(),
            'stats'  => [
                'total_users'  => User::count(),
                'admin_users'  => User::where('role', 'admin')->count(),
                'hr_users'     => User::where('role', 'hr')->count(),
                'active_users' => User::where('is_active', true)->count(),
            ],
        ]);
    }

    /**
     * Self-registrations awaiting HR/Admin approval.
     * Each is matched against an existing unlinked Employee dossier by email
     * so the approver can confirm it is really that person before linking.
     */
    private static function pendingQuery()
    {
        return User::whereNull('approved_at')->orderBy('created_at');
    }

    public function pending(Request $request)
    {
        $claims = self::pendingQuery()
            ->get()
            ->map(function (User $user) {
                $match = \App\Models\Employee::where('email', $user->email)
                    ->whereNull('user_id')
                    ->first();

                return [
                    'id'             => $user->id,
                    'name'           => $user->name,
                    'email'          => $user->email,
                    'registered_at'  => $user->created_at?->format('M d, Y h:i A'),
                    'matched_employee' => $match ? [
                        'employee_id'       => $match->employee_id,
                        'full_name'         => $match->full_name,
                        'department'        => $match->department,
                        'employment_status' => $match->employment_status,
                    ] : null,
                ];
            });

        return Inertia::render('Users/Pending', ['claims' => $claims]);
    }

    public function approveClaim(Request $request, User $user)
    {
        if (!is_null($user->approved_at)) {
            return back()->with('error', 'This registration was already processed.');
        }

        $employee = \App\Models\Employee::where('email', $user->email)
            ->whereNull('user_id')
            ->first();

        if ($employee) {
            // HR confirmed this registrant owns the pre-created dossier — link them
            $employee->update(['user_id' => $user->id]);
            $message = "Account approved and linked to employee {$employee->employee_id}.";
        } else {
            // No dossier exists: create a bare profile for HR to complete later
            $nameParts = explode(' ', trim($user->name), 2);
            \App\Models\Employee::create([
                'user_id'           => $user->id,
                'first_name'        => $nameParts[0],
                'last_name'         => $nameParts[1] ?? $nameParts[0],
                'email'             => $user->email,
                'birthdate'         => '1990-01-01', // placeholder — HR should update
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
            $message = 'Account approved. No matching employee record existed, so a new profile was created.';
        }

        $user->forceFill(['approved_at' => now(), 'is_active' => true])->save();
        LogsAudit::logAction('approve', 'user', "Approved registration of {$user->name} ({$user->email})");

        return redirect()->route('users.pending')->with('success', $message);
    }

    public function rejectClaim(Request $request, User $user)
    {
        if (!is_null($user->approved_at)) {
            return back()->with('error', 'This registration was already processed.');
        }
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot reject your own account.');
        }

        LogsAudit::logAction('reject', 'user', "Rejected registration of {$user->name} ({$user->email})");
        $user->delete();

        return redirect()->route('users.pending')->with('success', 'Registration rejected and removed.');
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