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
        'overtime_hours',
        'late_hours',
        'allowances',
        'deductions',
        'gross_pay',
        'net_pay',
    ];

    protected $casts = [
        'daily_rate'     => 'float',
        'days_worked'    => 'float',
        'overtime_hours' => 'float',
        'late_hours'     => 'float',
        'allowances'     => 'float',
        'deductions'     => 'float',
        'gross_pay'      => 'float',
        'net_pay'        => 'float',
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
            'overtime_hours' => $this->overtime_hours,
            'late_hours' => $this->late_hours,
        ];

        $deductions = [$this->deductions];
        $allowances = [$this->allowances];

        $result = $engine->compute(
            $employeeData,
            $attendance,
            $deductions,
            [],
            $allowances,
            [],
            false // not preview mode
        );

        $this->gross_pay = $result['gross_pay'];
        $this->net_pay = $result['net_pay'];

        \Log::info('Payroll computation complete', [
            'gross_pay' => $this->gross_pay,
            'net_pay' => $this->net_pay,
        ]);

        return $this;
    }
}