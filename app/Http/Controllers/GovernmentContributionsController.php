<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\SssContributionService;
use App\Services\PhilHealthContributionService;
use App\Services\PagIbigContributionService;
use Illuminate\Http\Request;
use App\Traits\LogsAudit;
class GovernmentContributionsController extends Controller
{
    protected $sssService;
    protected $philHealthService;
    protected $pagIbigService;

    public function __construct(SssContributionService $sssService, PhilHealthContributionService $philHealthService, PagIbigContributionService $pagIbigService)
    {
        $this->sssService = $sssService;
        $this->philHealthService = $philHealthService;
        $this->pagIbigService = $pagIbigService;
    }

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
        // Calculate SSS contribution based on salary bracket
        $sssContribution = $this->sssService->calculate($employee->basic_salary);
        // Use custom value if set, otherwise use computed
        $sssEmployeeShare = $employee->custom_sss_contribution;
        // Recalculate total if custom employee share is set
        if ($employee->custom_sss_contribution) {
            $sssContribution['total'] = $sssEmployeeShare;
        }

        // Calculate PhilHealth contribution based on salary basis
        $philHealthContribution = $this->philHealthService->calculate($employee->basic_salary);
        // Use custom value if set, otherwise use computed
        $philHealthEmployeeShare = $employee->custom_philhealth_contribution ;
        // Recalculate total if custom employee share is set
        if ($employee->custom_philhealth_contribution) {
            $philHealthContribution['total'] = $philHealthEmployeeShare;
        }

        // Calculate Pag-IBIG contribution based on salary range
        $pagIbigContribution = $this->pagIbigService->calculate($employee->basic_salary);
        // Use custom value if set, otherwise use computed
        $pagIbigEmployeeShare = $employee->custom_pagibig_contribution ?? $pagIbigContribution['employee_share'];
        // Recalculate total if custom employee share is set
        if ($employee->custom_pagibig_contribution) {
            $pagIbigContribution['total'] = $pagIbigEmployeeShare;
        }

        return view('government-contributions.show', compact(
            'employee',
            'sssContribution',
            'philHealthContribution',
            'pagIbigContribution',
            'sssEmployeeShare',
            'philHealthEmployeeShare',
            'pagIbigEmployeeShare'
        ));
    }

    public function update(Request $request, $employeeId)
    {
        $employee = Employee::where('employee_id', $employeeId)->firstOrFail();

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'custom_sss_contribution' => 'nullable|numeric|min:0',
            'custom_philhealth_contribution' => 'nullable|numeric|min:0',
            'custom_pagibig_contribution' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $employee->update($validator->validated());
LogsAudit::logAction('update', 'government_contributions', "Updated contributions for employee {$employee->employee_id}"); 
        return response()->json([
            'success' => true,
            'message' => 'Custom contributions updated successfully.'
        ]);
    }
}
