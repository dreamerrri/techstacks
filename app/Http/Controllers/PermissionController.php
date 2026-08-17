<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Traits\LogsAudit;
use Inertia\Inertia;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::with('roles')->get()->groupBy('module');
        return Inertia::render('Permissions/Index', compact('permissions'));
    }

    public function create()
    {
        return Inertia::render('Permissions/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions',
            'slug' => 'required|string|max:255|unique:permissions',
            'module' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $permission = Permission::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'module' => $validated['module'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        LogsAudit::logAction('create', 'permission', "Created permission: {$permission->name}");

        return redirect()->route('permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    public function show(Permission $permission)
    {
        $permission->load('roles');
        return Inertia::render('Permissions/Show', compact('permission'));
    }

    public function edit(Permission $permission)
    {
        return Inertia::render('Permissions/Edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'slug' => 'required|string|max:255|unique:permissions,slug,' . $permission->id,
            'module' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $permission->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'module' => $validated['module'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        LogsAudit::logAction('update', 'permission', "Updated permission: {$permission->name}");

        return redirect()->route('permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission)
    {
        $roleCount = $permission->roles()->count();

        if ($roleCount > 0) {
            return back()->with('error', 'Cannot delete permission assigned to roles.');
        }

        LogsAudit::logAction('delete', 'permission', "Deleted permission: {$permission->name}");

        $permission->delete();

        return redirect()->route('permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }
}