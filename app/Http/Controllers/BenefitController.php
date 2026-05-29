<?php

namespace App\Http\Controllers;

use App\Models\Benefit;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BenefitController extends Controller
{
    /**
     * Store a newly created benefit
     */
    public function store(Request $request, Employee $employee)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isHR()) {
            return back()->with('error', 'You do not have permission to add benefits.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:monthly,one-time',
            'description' => 'nullable|string',
            'effective_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:effective_date',
        ]);

        Benefit::create([
            'employee_id' => $employee->id,
            'name' => $validated['name'],
            'amount' => $validated['amount'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'effective_date' => $validated['effective_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
        ]);

        return back()->with('success', 'Benefit added successfully.');
    }

    /**
     * Update the specified benefit
     */
    public function update(Request $request, Employee $employee, Benefit $benefit)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isHR()) {
            return back()->with('error', 'You do not have permission to update benefits.');
        }

        if ($benefit->employee_id !== $employee->id) {
            return back()->with('error', 'Benefit does not belong to this employee.');
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

        $benefit->update([
            'name' => $validated['name'],
            'amount' => $validated['amount'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'effective_date' => $validated['effective_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
        ]);

        return back()->with('success', 'Benefit updated successfully.');
    }

    /**
     * Remove the specified benefit
     */
    public function destroy(Employee $employee, Benefit $benefit)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isHR()) {
            return back()->with('error', 'You do not have permission to delete benefits.');
        }

        if ($benefit->employee_id !== $employee->id) {
            return back()->with('error', 'Benefit does not belong to this employee.');
        }

        $benefit->delete();

        return back()->with('success', 'Benefit deleted successfully.');
    }
}
