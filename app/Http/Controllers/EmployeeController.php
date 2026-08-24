<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Traits\LogsAudit;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::active()->with('user');

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

        // Filter by employment status
        if ($request->filled('status')) {
            $query->where('employment_status', $request->status);
        }

        $allowedSorts = [
            'employment_status' => 'employment_status',
            'date_hired'        => 'date_hired',
        ];
        $sortBy  = $request->input('sort');
        $sortDir = $request->input('direction', 'asc') === 'desc' ? 'desc' : 'asc';
        $sortCol = isset($allowedSorts[$sortBy]) ? $allowedSorts[$sortBy] : 'last_name';

        $employees   = $query->orderBy($sortCol, $sortDir)->paginate(15)->withQueryString();
        $departments = Employee::active()->distinct()->orderBy('department')->pluck('department')->values();

        return Inertia::render('Employees/Index', [
            'employees'   => $employees,
            'departments' => $departments,
            'filters'     => $request->only(['search', 'department', 'status', 'sort', 'direction']),
            'stats'       => [
                'total'        => $employees->total(),
                'regular'      => Employee::active()->where('employment_status', 'Regular')->count(),
                'probationary' => Employee::active()->where('employment_status', 'Probationary')->count(),
                'archived'     => Employee::archived()->count(),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Employees/Create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateEmployee($request);

        $employee = Employee::create($validated);

        // --- FIX: Auto-create a linked User account when an employee is added ---
        $existingUser = User::where('email', $validated['email'])->first();

        if (!$existingUser) {
            // No user account exists yet — create one with a random temporary password
            $user = User::create([
                'name'      => $validated['first_name'] . ' ' . $validated['last_name'],
                'email'     => $validated['email'],
                'password'  => Hash::make(Str::random(16)), // temporary; user should reset
                'role'      => 'employee',
                'is_active' => true,
            ]);

            $employee->update(['user_id' => $user->id]);
        } else {
            // User already exists (e.g. registered before HR added them) — just link the records
            if (is_null($existingUser->employee?->id)) {
                $employee->update(['user_id' => $existingUser->id]);
            }
        }
        // --- END FIX ---

        return redirect()->route('employees.index')
            ->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee)
    {
        $employee->load(['user', 'activeAllowances', 'activeBenefits']);

        return Inertia::render('Employees/Show', [
            'employee'     => $employee,
            'payrollInput' => $employee->latestPayrollInput(),
        ]);
    }

    public function edit(Employee $employee)
    {
        return Inertia::render('Employees/Edit', ['employee' => $employee]);
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $this->validateEmployee($request, $employee->id);

        $employee->update($validated);

        // --- FIX: Keep the linked User name/email in sync if they changed ---
        if ($employee->user_id) {
            $linkedUser = User::find($employee->user_id);
            if ($linkedUser) {
                $linkedUser->update([
                    'name'  => $validated['first_name'] . ' ' . $validated['last_name'],
                    'email' => $validated['email'],
                ]);
            }
        }
        // --- END FIX ---

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Employee updated successfully.');
    }

    public function archive(Employee $employee)
{
    $employee->update(['is_archived' => true]);
    LogsAudit::logAction('archive', 'employee', "Archived employee {$employee->employee_id}"); // ADD

    return redirect()->route('employees.index')
        ->with('success', 'Employee archived successfully.');
}

    public function archived()
    {
        $employees = Employee::archived()->with('user')->orderBy('last_name')->paginate(15);
        return Inertia::render('Employees/Archived', ['employees' => $employees]);
    }

    public function restore(Employee $employee)
{
    $employee->update(['is_archived' => false]);
    LogsAudit::logAction('restore', 'employee', "Restored employee {$employee->employee_id}"); // ADD

    return redirect()->route('employees.archived')
        ->with('success', 'Employee restored successfully.');
}

    public function updateGovContributions(Request $request, Employee $employee)
    {
        // Authorization: Only HR and Admin can update government contributions
        if (!auth()->user()->isAdmin() && !auth()->user()->isHR()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'custom_sss_contribution' => 'nullable|numeric|min:0',
            'custom_philhealth_contribution' => 'nullable|numeric|min:0',
            'custom_pagibig_contribution' => 'nullable|numeric|min:0',
        ]);

        $employee->update($validated);
        LogsAudit::logAction('update', 'government_contributions', "Updated contributions for employee {$employee->employee_id}");

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Government contribution rates updated successfully.');
    }

    private function validateEmployee(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            'first_name'         => 'required|string|max:100',
            'middle_name'        => 'nullable|string|max:100',
            'last_name'          => 'required|string|max:100',
            'birthdate'          => 'required|date|before:today',
            'gender'             => 'required|in:Male,Female,Other',
            'civil_status'       => 'required|in:Single,Married,Widowed,Separated',
            'address'            => 'required|string',
            'contact_number'     => 'required|string|max:20',
            'email'              => 'required|email|unique:employees,email' . ($ignoreId ? ",{$ignoreId}" : ''),
            'department'         => 'required|string|max:100',
            'position'           => 'required|string|max:100',
            'employment_status'  => 'required|in:Regular,Probationary,Contractual,Part-time',
            'date_hired'         => 'required|date',
            'salary_type'        => 'required|in:Monthly,Daily,Hourly',
            'basic_salary'       => 'required|numeric|min:0',
            'sss_number'         => 'nullable|string|max:20',
            'philhealth_number'  => 'nullable|string|max:20',
            'pagibig_number'     => 'nullable|string|max:20',
            'tin_number'         => 'nullable|string|max:20',
        ]);
    }
}