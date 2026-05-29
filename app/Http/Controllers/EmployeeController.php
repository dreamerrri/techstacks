<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeController extends Controller
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

        // Filter by employment status
        if ($request->filled('status')) {
            $query->where('employment_status', $request->status);
        }

        $employees   = $query->orderBy('last_name')->paginate(15)->withQueryString();
        $departments = Employee::active()->distinct()->pluck('department');

        return view('employees.index', compact('employees', 'departments'));
    }

    public function create()
    {
        return view('employees.create');
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
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        return view('employees.edit', compact('employee'));
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

        return redirect()->route('employees.index')
            ->with('success', 'Employee archived successfully.');
    }

    public function archived()
    {
        $employees = Employee::archived()->orderBy('last_name')->paginate(15);
        return view('employees.archived', compact('employees'));
    }

    public function restore(Employee $employee)
    {
        $employee->update(['is_archived' => false]);

        return redirect()->route('employees.archived')
            ->with('success', 'Employee restored successfully.');
    }

    private function validateEmployee(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            'employee_id'        => 'required|string|unique:employees,employee_id' . ($ignoreId ? ",{$ignoreId}" : ''),
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

    // -----------------------------------------------------------------------
    // API
    // -----------------------------------------------------------------------

    // GET /api/employees
    public function apiIndex(Request $request)
    {
        $query = Employee::active();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department'))
            $query->where('department', $request->department);

        if ($request->filled('status'))
            $query->where('employment_status', $request->status);

        return response()->json(['success' => true, 'data' => $query->paginate(15)]);
    }

    // GET /api/employees/{id}
    public function apiShow(Employee $employee)
    {
        return response()->json(['success' => true, 'data' => $employee]);
    }

    // POST /api/employees
    public function apiStore(Request $request)
    {
        $validated = $this->validateEmployee($request);
        $employee  = Employee::create($validated);

        // --- FIX: Also auto-create a User account via API ---
        $existingUser = User::where('email', $validated['email'])->first();

        if (!$existingUser) {
            $user = User::create([
                'name'      => $validated['first_name'] . ' ' . $validated['last_name'],
                'email'     => $validated['email'],
                'password'  => Hash::make(Str::random(16)),
                'role'      => 'employee',
                'is_active' => true,
            ]);
            $employee->update(['user_id' => $user->id]);
        } else {
            $employee->update(['user_id' => $existingUser->id]);
        }
        // --- END FIX ---

        return response()->json(['success' => true, 'message' => 'Employee created.', 'data' => $employee], 201);
    }

    // PUT /api/employees/{id}
    public function apiUpdate(Request $request, Employee $employee)
    {
        $validated = $this->validateEmployee($request, $employee->id);
        $employee->update($validated);
        return response()->json(['success' => true, 'message' => 'Employee updated.', 'data' => $employee]);
    }

    // PATCH /api/employees/{id}/archive
    public function apiArchive(Employee $employee)
    {
        $employee->update(['is_archived' => true]);
        return response()->json(['success' => true, 'message' => 'Employee archived.']);
    }
}