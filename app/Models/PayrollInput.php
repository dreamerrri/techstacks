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
        'regular_hours',
        'overtime_hours',
        'late_hours',
        'holiday_days',
        'night_differential_hours',
        'allowances',
        'deductions',
        'gross_pay',
        'net_pay',
    ];

    protected $casts = [
        'daily_rate'              => 'float',
        'days_worked'             => 'float',
        'regular_hours'          => 'float',
        'overtime_hours'          => 'float',
        'late_hours'              => 'float',
        'holiday_days'            => 'float',
        'night_differential_hours' => 'float',
        'allowances'              => 'float',
        'deductions'              => 'float',
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
        
        // Convert daily rate to monthly salary using the formula: daily_rate * 22
        $monthlySalary = $this->daily_rate * 22;
        
        $employeeData = [
            'monthly_salary' => $monthlySalary,
            'working_days_per_month' => 22,
            'working_hours_per_day' => 8,
        ];

        $attendance = [
            'days_worked' => $this->days_worked,
            'regular_hours' => $this->regular_hours ?? 0,
            'overtime_hours' => $this->overtime_hours,
            'late_hours' => $this->late_hours,
            'holiday_days' => $this->holiday_days ?? 0,
            'night_differential_hours' => $this->night_differential_hours ?? 0,
        ];

        $allowances = [$this->allowances];

        // First, calculate gross pay without deductions to get contribution bases
        $previewResult = $engine->compute($employeeData, $attendance, [], [], $allowances, [], true);
        $grossPay = $previewResult['gross_pay'];

        // Get employee for government contribution rates
        $employee = $this->employee;

        // Government contributions are based on gross pay
        // (same as in ManualPayrollAttendanceController::preview() and PayrollController::calculatePayroll() for consistency)
        // SSS Contribution using official bracket table (Circular No. 2024-006)
        // Use custom value if set, otherwise use computed value
        $sssService = new \App\Services\SssContributionService();
        $sssCalculation = $sssService->calculate($grossPay);
        $sssContribution = $employee->custom_sss_contribution ?? $sssCalculation['employee_share'];

        // PhilHealth Contribution using official 2025/2026 table
        // Use custom value if set, otherwise use computed value
        $philHealthService = new \App\Services\PhilHealthContributionService();
        $philHealthCalculation = $philHealthService->calculate($grossPay);
        $philhealthContribution = $employee->custom_philhealth_contribution ?? $philHealthCalculation['employee_share'];

        // Pag-IBIG Contribution using official 2026 table
        // Use custom value if set, otherwise use computed value
        $pagIbigService = new \App\Services\PagIbigContributionService();
        $pagIbigCalculation = $pagIbigService->calculate($grossPay);
        $pagibigContribution = $employee->custom_pagibig_contribution ?? $pagIbigCalculation['employee_share'];

        // Calculate withholding tax
        $totalContributions = $sssContribution + $philhealthContribution + $pagibigContribution;
        \Log::info('Withholding tax calculation in PayrollInput', [
            'gross_pay' => $grossPay,
            'sss_contribution' => $sssContribution,
            'philhealth_contribution' => $philhealthContribution,
            'pagibig_contribution' => $pagibigContribution,
            'total_contributions' => $totalContributions,
        ]);
        $withholdingTax = $this->calculateTax($grossPay, $totalContributions);
        \Log::info('Withholding tax result in PayrollInput', ['withholding_tax' => $withholdingTax]);

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
            [],
            $allowances,
            [],
            false // not preview mode for final calculation
        );

        $this->gross_pay = $result['gross_pay'];
        $this->net_pay = $result['net_pay'];

        \Log::info('Payroll computation complete', [
            'gross_pay' => $this->gross_pay,
            'net_pay' => $this->net_pay,
            'government_deductions' => $governmentDeductions,
            'manual_deductions' => $manualDeductions,
        ]);

        return $this;
    }

    /**
     * Calculate withholding tax based on annual computation
     * Formula: (Salary - Contributions) * 12 = Annual Total
     * Annual Total - 250,000 = Taxable Annual Total
     * Taxable Annual Total * 15% = Tax Rate
     * Tax Rate / 12 = Gross Pay Tax
     */
    private function calculateTax(float $grossPay, float $totalContributions): float
    {
        // Calculate annual total: (Salary - Contributions) * 12
        $annualTotal = ($grossPay - $totalContributions) * 12;
        
        // Subtract tax exemption (250,000 from income tax table)
        $taxableAnnualTotal = $annualTotal - 250000;
        
        // If taxable annual total is zero or negative, no tax
        if ($taxableAnnualTotal <= 0) {
            return 0;
        }
        
        // Apply 15% tax rate (graduated tax rates from tax table)
        $taxRate = $taxableAnnualTotal * 0.15;
        
        // Convert back to monthly tax
        $grossPayTax = $taxRate / 12;
        
        return round($grossPayTax, 2);
    }
}