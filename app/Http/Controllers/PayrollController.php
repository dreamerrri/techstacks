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

        // Calculate hourly and daily rates
        $hourlyRate = match ($salaryType) {
            'Monthly' => $basicSalary / 22 / 8, // 22 days, 8 hours per day
            'Daily' => $basicSalary / 8,
            'Hourly' => $basicSalary,
            default => $basicSalary / 22 / 8,
        };

        $dailyRate = match ($salaryType) {
            'Monthly' => $basicSalary / 22,
            'Daily' => $basicSalary,
            'Hourly' => $basicSalary * 8,
            default => $basicSalary / 22,
        };

        // Get attendance data for current month
        $attendance = $employee->currentAttendance();

        // Calculate base salary based on actual days worked
        $daysWorked = $attendance->days_worked ?? 0;
        $regularHours = $attendance->regular_hours ?? 0;
        $overtimeHours = $attendance->overtime_hours ?? 0;
        $lateHours = $attendance->late_hours ?? 0;
        $nightDifferentialHours = $attendance->night_differential_hours ?? 0;
        $regularHolidayWorked = $attendance->regular_holiday_worked ?? 0;

        // Calculate base pay based on actual hours worked
        $basePay = $regularHours * $hourlyRate;

        // Calculate overtime pay (1.25x of hourly rate)
        $overtimePay = $overtimeHours * $hourlyRate * 1.25;

        // Calculate night differential pay (10% additional of hourly rate)
        $nightDifferentialPay = $nightDifferentialHours * $hourlyRate * 0.10;

        // Calculate holiday pay (2x of daily rate for regular holidays)
        $holidayPay = $regularHolidayWorked * $dailyRate * 2;

        // Calculate late deduction (deduct hourly rate for late hours)
        $lateDeduction = $lateHours * $hourlyRate;

        // Calculate gross pay
        $grossPay = $basePay + $overtimePay + $nightDifferentialPay + $holidayPay - $lateDeduction;

        // Government contributions (Philippines standard rates)
        // SSS: 4.5% of gross pay (capped)
        $sssContribution = min($grossPay * 0.045, 900);

        // PhilHealth: 2.25% of gross pay (capped)
        $philhealthContribution = min($grossPay * 0.0225, 1500);

        // Pag-IBIG: 2% of gross pay (capped at 100)
        $pagibigContribution = min($grossPay * 0.02, 100);

        // Withholding Tax (simplified calculation)
        // Tax brackets for monthly income (Philippines)
        $taxableIncome = $grossPay - $sssContribution - $philhealthContribution - $pagibigContribution;
        $withholdingTax = $this->calculateTax($taxableIncome);

        // Total deductions
        $totalDeductions = $sssContribution + $philhealthContribution + $pagibigContribution + $withholdingTax + $lateDeduction;

        // Net pay
        $netPay = $grossPay - $totalDeductions;

        return [
            'basic_salary' => $basicSalary,
            'salary_type' => $salaryType,
            'hourly_rate' => $hourlyRate,
            'daily_rate' => $dailyRate,
            'base_pay' => $basePay,
            'overtime_pay' => $overtimePay,
            'night_differential_pay' => $nightDifferentialPay,
            'holiday_pay' => $holidayPay,
            'late_deduction' => $lateDeduction,
            'gross_pay' => $grossPay,
            'sss_contribution' => $sssContribution,
            'philhealth_contribution' => $philhealthContribution,
            'pagibig_contribution' => $pagibigContribution,
            'withholding_tax' => $withholdingTax,
            'total_deductions' => $totalDeductions,
            'net_pay' => $netPay,
            'taxable_income' => $taxableIncome,
            'attendance_data' => [
                'days_worked' => $daysWorked,
                'regular_hours' => $regularHours,
                'overtime_hours' => $overtimeHours,
                'late_hours' => $lateHours,
                'night_differential_hours' => $nightDifferentialHours,
                'regular_holiday_worked' => $regularHolidayWorked,
            ],
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
