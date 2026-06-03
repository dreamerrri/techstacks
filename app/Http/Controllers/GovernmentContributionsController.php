<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\SssContributionService;
use App\Services\PhilHealthContributionService;
use App\Services\PagIbigContributionService;
use Illuminate\Http\Request;

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

        // Calculate PhilHealth contribution based on salary basis
        $philHealthContribution = $this->philHealthService->calculate($employee->basic_salary);

        // Calculate Pag-IBIG contribution based on salary range
        $pagIbigContribution = $this->pagIbigService->calculate($employee->basic_salary);

        return view('government-contributions.show', compact('employee', 'sssContribution', 'philHealthContribution', 'pagIbigContribution'));
    }
}
