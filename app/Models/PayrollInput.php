<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollInput extends Model
{
    protected $fillable = [
        'payroll_period_id',
        'employee_id',
        'daily_rate',
        'rate_type',
        'days_worked',
        'weekends_worked',
        'overtime_hours',
        'late_hours',
        'holiday_days',
        'night_differential_hours',
        'allowances',
        'deductions',
        'deductions_remarks',
        'reimbursements',
        'reimbursements_remarks',
        'gross_pay',
        'net_pay',
     
    ];

    protected $casts = [
        'daily_rate'              => 'float',
        'days_worked'             => 'float',
        'weekends_worked'          => 'float',
        'overtime_hours'          => 'float',
        'late_hours'              => 'float',
        'holiday_days'            => 'float',
        'night_differential_hours' => 'float',
        'allowances'              => 'float',
        'deductions'              => 'float',
        'reimbursements'          => 'float',
        'gross_pay'               => 'float',
        'net_pay'                 => 'float',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(PayrollAdjustment::class);
    }

    // ── Computation ────────────────────────────────────────────

    /**
     * Compute and assign gross_pay and net_pay from current field values.
     * Call before saving: $input->computePay()->save();
     */
    public function computePay(): static
    {
        \Log::info('Computing pay for payroll input', [
            'daily_rate' => $this->daily_rate,
            'days_worked' => $this->days_worked,
            'overtime_hours' => $this->overtime_hours,
            'late_hours' => $this->late_hours,
            'holiday_days' => $this->holiday_days,
            'night_differential_hours' => $this->night_differential_hours,
            'allowances' => $this->allowances,
            'deductions' => $this->deductions,
        ]);

        // Use PayrollComputationEngine for consistency
        $engine = new \App\Services\Payroll\PayrollComputationEngine();
        
        // Get cutoff dates from payroll period if available
        $cutoffStart = null;
        $cutoffEnd = null;
        $workingDaysPerMonth = 22; // Default fallback
        if ($this->payrollPeriod) {
            $cutoffStart = $this->payrollPeriod->cutoff_start->toDateString();
            $cutoffEnd = $this->payrollPeriod->cutoff_end->toDateString();
            $workingDaysPerMonth = \App\Models\PayrollPeriod::calculateWorkingDays($cutoffStart, $cutoffEnd);
        }
        
        $employeeData = [
            'daily_rate' => $this->daily_rate,
            'working_hours_per_day' => 8,
        ];

        $attendance = [
            'days_worked' => $this->days_worked,
            'weekends_worked' => $this->weekends_worked ?? 0,
            'overtime_hours' => $this->overtime_hours,
            'late_hours' => $this->late_hours,
            'holiday_days' => $this->holiday_days ?? 0,
            'night_differential_hours' => $this->night_differential_hours ?? 0,
        ];

        $allowances = [$this->allowances];

        // Get employee for government contribution rates and benefits
        $employee = $this->employee;

        // Fetch active benefits from employee (for consistency with PayrollController::calculatePayroll())
        $benefits = $employee ? $employee->activeBenefits()->pluck('amount')->toArray() : [];

        // First, calculate gross pay without deductions to get contribution bases
        $previewResult = $engine->compute($employeeData, $attendance, [], $benefits, $allowances, [], true, $cutoffStart, $cutoffEnd);
        $grossPay = $previewResult['gross_pay'];

        // Government contributions are fixed based on monthly basic salary and divided per cutoff (semi-monthly)
        // SSS Contribution using official bracket table (Circular No. 2024-006)
        $sssService = new \App\Services\SssContributionService();
        $sssCalculation = $sssService->calculate($employee ? $employee->basic_salary : 0);
        $sssMonthly = $employee->custom_sss_contribution ?? $sssCalculation['employee_share'];
        $sssContribution = round($sssMonthly / 2, 2);

        // PhilHealth Contribution using official 2025/2026 table
        $philHealthService = new \App\Services\PhilHealthContributionService();
        $philHealthCalculation = $philHealthService->calculate($employee ? $employee->basic_salary : 0);
        $philHealthMonthly = $employee->custom_philhealth_contribution ?? $philHealthCalculation['employee_share'];
        $philhealthContribution = round($philHealthMonthly / 2, 2);

        // Pag-IBIG Contribution using official 2026 table
        $pagIbigService = new \App\Services\PagIbigContributionService();
        $pagIbigCalculation = $pagIbigService->calculate($employee ? $employee->basic_salary : 0);
        $pagIbigMonthly = $employee->custom_pagibig_contribution ?? $pagIbigCalculation['employee_share'];
        $pagibigContribution = round($pagIbigMonthly / 2, 2);

        // Calculate withholding tax only if period is in second half of month (16-30,31)
        $withholdingTax = 0;
        if ($this->payrollPeriod && $this->payrollPeriod->isSecondHalfOfMonth()) {
            $totalContributions = $sssContribution + $philhealthContribution + $pagibigContribution;
            
            // Fetch 1st cutoff pay for the same month
            $firstCutoffGrossPay = 0;
            $firstCutoffPeriod = \App\Models\PayrollPeriod::whereYear('cutoff_start', $this->payrollPeriod->cutoff_start->year)
                ->whereMonth('cutoff_start', $this->payrollPeriod->cutoff_start->month)
                ->whereDay('cutoff_start', '<=', 15)
                ->first();
            
            if ($firstCutoffPeriod) {
                $firstCutoffPayrollInput = \App\Models\PayrollInput::where('payroll_period_id', $firstCutoffPeriod->id)
                    ->where('employee_id', $this->employee_id)
                    ->first();
                if ($firstCutoffPayrollInput) {
                    $firstCutoffGrossPay = $firstCutoffPayrollInput->gross_pay;
                }
            }
            
            $totalMonthlyGross = $firstCutoffGrossPay + $grossPay;
            $totalMonthlyContributions = $totalContributions * 2; // Since contributions are halved per cutoff
            
            // Use total monthly allowances for withholding tax calculation
            // Since allowances are divided by 2 per cutoff, multiply by 2 to get total monthly
            $totalMonthlyAllowances = $this->allowances * 2;

            \Log::info('Withholding tax calculation in PayrollInput', [
                'total_monthly_gross' => $totalMonthlyGross,
                'total_monthly_contributions' => $totalMonthlyContributions,
                'total_monthly_allowances' => $totalMonthlyAllowances,
            ]);
            $withholdingTax = $this->calculateTax($totalMonthlyGross, $totalMonthlyContributions, $totalMonthlyAllowances);
            \Log::info('Withholding tax result in PayrollInput', ['withholding_tax' => $withholdingTax]);
        }

        // Total government contributions
        $governmentDeductions = $sssContribution + $philhealthContribution + $pagibigContribution + $withholdingTax;

        // Add manual deductions
        $manualDeductions = $this->deductions;

        // Total deductions
        $totalDeductions = $governmentDeductions + $manualDeductions;

        // Calculate final payroll with all deductions
        $result = $engine->compute(
            $employeeData,
            $attendance,
            [$totalDeductions],
            $benefits,
            $allowances,
            [],
            false, // not preview mode for final calculation
            $cutoffStart,
            $cutoffEnd
        );

        $this->gross_pay = $result['gross_pay'];
        $this->net_pay = $result['net_pay'] + ($this->reimbursements ?? 0);

        \Log::info('Payroll computation complete', [
            'gross_pay' => $this->gross_pay,
            'net_pay' => $this->net_pay,
            'government_deductions' => $governmentDeductions,
            'manual_deductions' => $manualDeductions,
        ]);

        return $this;
    }

    /**
     * Calculate withholding tax based on monthly computation
     * Formula: (Total Monthly Gross - Total Monthly Contributions - Total Monthly Allowances) - 33,333 = taxablePay
     * taxablePay * 20% + 1875 = Withholding Tax
     */
    private function calculateTax(float $totalMonthlyGross, float $totalMonthlyContributions, float $totalMonthlyAllowances = 0): float
    {
        // Calculate taxable income: Total Gross - Total Monthly Contributions - Total Monthly Allowances
        // Allowances are considered as advance paychecks and should be deducted from taxable income
        $taxableIncome = $totalMonthlyGross - $totalMonthlyContributions - $totalMonthlyAllowances;
        
        // Subtract lower limit of bracket (33,333) to get taxablePay (excess)
        $taxablePay = $taxableIncome - 33333;
        
        // If taxable income is below 33,333, no tax for this bracket
        if ($taxablePay <= 0) {
            return 0;
        }
        
        // Apply formula: taxablePay * 20% + 1875
        $withholdingTax = ($taxablePay * 0.20) + 1875;
        
        return round($withholdingTax, 2);
    }
}