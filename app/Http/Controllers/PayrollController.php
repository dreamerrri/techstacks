<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
    /**
     * Show payroll preview for employees
     * Admin and HR can see all employees' payroll
     * Regular employees can only see their own payroll
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();
        $isHR = $user->isHR();

        // Filter employees based on role
        if ($isAdmin || $isHR) {
            // Admin and HR can see all active employees
            $query = Employee::active();
        } else {
            // Regular employees can only see their own payroll
            $employee = $user->employee;
            if (!$employee) {
                return redirect()->route('dashboard')
                    ->with('error', 'No employee record found for your account.');
            }
            $query = Employee::where('id', $employee->id);
        }

        // Search functionality
        if ($request->filled('search') && ($isAdmin || $isHR)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        // Filter by department (admin and HR only)
        if ($request->filled('department') && ($isAdmin || $isHR)) {
            $query->where('department', $request->department);
        }

        $employees = $query->orderBy('last_name')->paginate(15)->withQueryString();
        $departments = Employee::active()->distinct()->pluck('department');

        // Calculate payroll for each employee
        $payrollData = [];
        foreach ($employees as $employee) {
            $payrollData[$employee->id] = $this->calculatePayroll($employee);
        }

        return view('payroll.index', compact('employees', 'departments', 'payrollData', 'isAdmin', 'isHR'));
    }

    /**
     * Show detailed payroll preview for a specific employee
     */
    public function show(Employee $employee)
    {
        $user = Auth::user();
        $isAdmin = $user->isAdmin();
        $isHR = $user->isHR();

        // Check access: admin and HR can see any employee, regular employees can only see themselves
        if (!$isAdmin && !$isHR) {
            $userEmployee = $user->employee;
            if (!$userEmployee || $userEmployee->id !== $employee->id) {
                return redirect()->route('payroll.index')
                    ->with('error', 'You can only view your own payroll.');
            }
        }

        $payrollData = $this->calculatePayroll($employee);

        return view('payroll.show', compact('employee', 'payrollData', 'isAdmin', 'isHR'));
    }

    /**
     * Calculate payroll for an employee
     */
    private function calculatePayroll(Employee $employee): array
    {
        $basicSalary = $employee->basic_salary;
        $salaryType = $employee->salary_type;

        // Calculate monthly salary based on salary type
        $monthlySalary = match ($salaryType) {
            'Monthly' => $basicSalary,
            'Daily' => $basicSalary * 22, // Assuming 22 working days per month
            'Hourly' => $basicSalary * 8 * 22, // 8 hours/day, 22 days/month
            default => $basicSalary,
        };

        // Government contributions (Philippines standard rates)
        // SSS: 4.5% of monthly salary (capped)
        $sssContribution = min($monthlySalary * 0.045, 900);

        // PhilHealth: 2.25% of monthly salary (capped)
        $philhealthContribution = min($monthlySalary * 0.0225, 1500);

        // Pag-IBIG: 2% of monthly salary (capped at 100)
        $pagibigContribution = min($monthlySalary * 0.02, 100);

        // Withholding Tax (simplified calculation)
        // Tax brackets for monthly income (Philippines)
        $taxableIncome = $monthlySalary - $sssContribution - $philhealthContribution - $pagibigContribution;
        $withholdingTax = $this->calculateTax($taxableIncome);

        // Net pay
        $netPay = $monthlySalary - $sssContribution - $philhealthContribution - $pagibigContribution - $withholdingTax;

        return [
            'basic_salary' => $basicSalary,
            'salary_type' => $salaryType,
            'monthly_salary' => $monthlySalary,
            'sss_contribution' => $sssContribution,
            'philhealth_contribution' => $philhealthContribution,
            'pagibig_contribution' => $pagibigContribution,
            'total_deductions' => $sssContribution + $philhealthContribution + $pagibigContribution + $withholdingTax,
            'withholding_tax' => $withholdingTax,
            'net_pay' => $netPay,
            'taxable_income' => $taxableIncome,
        ];
    }

    /**
     * Calculate withholding tax based on Philippine tax brackets
     */
    private function calculateTax(float $taxableIncome): float
    {
        if ($taxableIncome <= 20832) {
            return 0;
        } elseif ($taxableIncome <= 33333) {
            return ($taxableIncome - 20832) * 0.20;
        } elseif ($taxableIncome <= 66667) {
            return 2500 + ($taxableIncome - 33333) * 0.25;
        } elseif ($taxableIncome <= 166667) {
            return 10833.33 + ($taxableIncome - 66667) * 0.30;
        } elseif ($taxableIncome <= 666667) {
            return 40833.33 + ($taxableIncome - 166667) * 0.32;
        } else {
            return 200833.33 + ($taxableIncome - 666667) * 0.35;
        }
    }
}
