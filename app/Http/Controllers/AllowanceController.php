<?php

namespace App\Http\Controllers;

use App\Models\Allowance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AllowanceController extends Controller
{
    /**
     * Store a newly created allowance
     */
    public function store(Request $request, Employee $employee)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isHR()) {
            return back()->with('error', 'You do not have permission to add allowances.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:monthly,one-time',
            'description' => 'nullable|string',
            'effective_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:effective_date',
        ]);

        Allowance::create([
            'employee_id' => $employee->id,
            'name' => $validated['name'],
            'amount' => $validated['amount'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'effective_date' => $validated['effective_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
        ]);

        return back()->with('success', 'Allowance added successfully.');
    }

    /**
     * Update the specified allowance
     */
    public function update(Request $request, Employee $employee, Allowance $allowance)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isHR()) {
            return back()->with('error', 'You do not have permission to update allowances.');
        }

        if ($allowance->employee_id !== $employee->id) {
            return back()->with('error', 'Allowance does not belong to this employee.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:monthly,one-time',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'effective_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:effective_date',
        ]);

        $allowance->update([
            'name' => $validated['name'],
            'amount' => $validated['amount'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'effective_date' => $validated['effective_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
        ]);

        return back()->with('success', 'Allowance updated successfully.');
    }

    /**
     * Remove the specified allowance
     */
    public function destroy(Employee $employee, Allowance $allowance)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isHR()) {
            return back()->with('error', 'You do not have permission to delete allowances.');
        }

        if ($allowance->employee_id !== $employee->id) {
            return back()->with('error', 'Allowance does not belong to this employee.');
        }

        $allowance->delete();

        return back()->with('success', 'Allowance deleted successfully.');
    }
}
