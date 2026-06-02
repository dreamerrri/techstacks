<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->withCount('users')->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::active()->get()->groupBy('module');
        return view('roles.create', compact('permissions'));
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

        Log::info('Role created', ['role_id' => $role->id, 'user_id' => auth()->id()]);

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function show(Role $role)
    {
        $role->load('permissions', 'users');
        $availableUsers = User::where('role', '!=', $role->slug)->get();
        return view('roles.show', compact('role', 'availableUsers'));
    }

    public function edit(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::active()->get()->groupBy('module');
        return view('roles.edit', compact('role', 'permissions'));
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

        $oldValues = $role->toArray();
        $role->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        Log::info('Role updated', [
            'role_id' => $role->id,
            'user_id' => auth()->id(),
            'old_values' => $oldValues,
            'new_values' => $role->toArray(),
        ]);

        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        $userCount = $role->users()->count();
        
        if ($userCount > 0) {
            return back()->with('error', 'Cannot delete role with assigned users.');
        }

        $role->delete();

        Log::info('Role deleted', ['role_id' => $role->id, 'user_id' => auth()->id()]);

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

        Log::info('User assigned to role', [
            'role_id' => $role->id,
            'user_id' => $user->id,
            'assigned_by' => auth()->id(),
        ]);

        return back()->with('success', "User {$user->name} assigned to {$role->name} role successfully.");
    }

    public function removeUser(Request $request, Role $role, User $user)
    {
        if (!$role->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'User does not have this role.');
        }

        $role->users()->detach($user->id);
        $user->update(['role' => 'employee']);

        Log::info('User removed from role', [
            'role_id' => $role->id,
            'user_id' => $user->id,
            'removed_by' => auth()->id(),
        ]);

        return back()->with('success', "User {$user->name} removed from {$role->name} role successfully.");
    }
}
