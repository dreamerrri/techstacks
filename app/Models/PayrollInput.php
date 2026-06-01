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
        
        // Convert rate to monthly salary based on rate type
        $monthlySalary = ($this->rate_type === 'monthly') ? $this->daily_rate : ($this->daily_rate * 22);
        
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
        
        // Government contributions (use employee-specific rates or default Philippine standard rates)
        $sssRate = $employee->sss_rate ?? 0.045;
        $sssCap = $employee->sss_cap ?? 900;
        $philhealthRate = $employee->philhealth_rate ?? 0.0225;
        $philhealthCap = $employee->philhealth_cap ?? 1500;
        $pagibigRate = $employee->pagibig_rate ?? 0.02;
        $pagibigCap = $employee->pagibig_cap ?? 100;

        $sssContribution = min($grossPay * $sssRate, $sssCap);
        $philhealthContribution = min($grossPay * $philhealthRate, $philhealthCap);
        $pagibigContribution = min($grossPay * $pagibigRate, $pagibigCap);

        // Calculate withholding tax
        $taxableIncome = $grossPay - $sssContribution - $philhealthContribution - $pagibigContribution;
        $withholdingTax = $this->calculateTax($taxableIncome);

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