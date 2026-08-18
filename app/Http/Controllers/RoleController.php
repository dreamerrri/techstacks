<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\LogsAudit;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->withCount('users')->get();
        return Inertia::render('Roles/Index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::active()->get()->groupBy('module');
        return Inertia::render('Roles/Create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'slug' => 'required|string|max:255|unique:roles',
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
            'is_active' => 'boolean',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        LogsAudit::logAction('create', 'role', "Created role: {$role->name}");

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function show(Role $role)
    {
        $role->load('permissions', 'users');
        $availableUsers = User::where('role', '!=', $role->slug)->get();
        return Inertia::render('Roles/Show', compact('role', 'availableUsers'));
    }

    public function edit(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::active()->get()->groupBy('module');
        return Inertia::render('Roles/Edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'slug' => 'required|string|max:255|unique:roles,slug,' . $role->id,
            'description' => 'nullable|string',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
            'is_active' => 'boolean',
        ]);

        $role->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        LogsAudit::logAction('update', 'role', "Updated role: {$role->name}");
        LogsAudit::logAction('update', 'permission_role', "Synced permissions for role: {$role->name}");

        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        $userCount = $role->users()->count();

        if ($userCount > 0) {
            return back()->with('error', 'Cannot delete role with assigned users.');
        }

        LogsAudit::logAction('delete', 'role', "Deleted role: {$role->name}");

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    public function assignUser(Request $request, Role $role)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);

        if ($role->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'User already has this role.');
        }

        $role->users()->attach($user->id);
        $user->update(['role' => $role->slug]);

        LogsAudit::logAction('assign', 'role', "Assigned role {$role->name} to user {$user->name}");

        return back()->with('success', "User {$user->name} assigned to {$role->name} role successfully.");
    }

    public function removeUser(Request $request, Role $role, User $user)
    {
        if (!$role->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'User does not have this role.');
        }

        $role->users()->detach($user->id);
        $user->update(['role' => 'employee']);

        LogsAudit::logAction('revoke', 'role', "Removed role {$role->name} from user {$user->name}");

        return back()->with('success', "User {$user->name} removed from {$role->name} role successfully.");
    }
}