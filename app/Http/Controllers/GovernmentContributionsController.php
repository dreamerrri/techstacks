<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class GovernmentContributionsController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::active();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by department
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        $employees = $query->orderBy('last_name')->paginate(15)->withQueryString();
        $departments = Employee::active()->distinct()->pluck('department');

        return view('government-contributions.index', compact('employees', 'departments'));
    }

    public function show(Employee $employee)
    {
        return view('government-contributions.show', compact('employee'));
    }

    public function update(Request $request, Employee $employee)
    {
        // Authorization: Only HR and Admin can update government contributions
        if (!auth()->user()->isAdmin() && !auth()->user()->isHR()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'sss_rate' => 'required|numeric|min:0|max:1',
            'sss_cap' => 'required|numeric|min:0',
            'philhealth_rate' => 'required|numeric|min:0|max:1',
            'philhealth_cap' => 'required|numeric|min:0',
            'pagibig_rate' => 'required|numeric|min:0|max:1',
            'pagibig_cap' => 'required|numeric|min:0',
            'withholding_tax_rate' => 'required|numeric|min:0|max:1',
        ]);

        $employee->update($validated);

        return redirect()->route('government-contributions.show', $employee)
            ->with('success', 'Government contribution rates updated successfully.');
    }
}
