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
        $hourlyRate = $this->daily_rate / 8;

        $this->gross_pay = round(
            ($this->days_worked * $this->daily_rate)
            + ($this->overtime_hours * $hourlyRate)
            - ($this->late_hours * $hourlyRate)
            + $this->allowances,
            2
        );

        $this->gross_pay = max(0, $this->gross_pay);

        $this->net_pay = round(
            max(0, $this->gross_pay - $this->deductions),
            2
        );

        return $this;
    }
}