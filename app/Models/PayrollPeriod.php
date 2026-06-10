<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollPeriod extends Model
{
    protected $fillable = [
        'cutoff_start',
        'cutoff_end',
        'payroll_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'cutoff_start' => 'date',
        'cutoff_end'   => 'date',
        'payroll_date' => 'date',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function payrollInputs(): HasMany
    {
        return $this->hasMany(PayrollInput::class);
    }

    /**
     * Get payroll inputs or return empty collection if null
     */
    public function getPayrollInputsAttribute()
    {
        return $this->getRelationValue('payrollInputs') ?? $this->payrollInputs()->get();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Helpers ────────────────────────────────────────────────

    public function isFinalized(): bool
    {
        return $this->status === 'finalized';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function getTotalGrossPayAttribute(): float
    {
        return $this->payrollInputs ? $this->payrollInputs->sum('gross_pay') : 0;
    }

    public function getTotalNetPayAttribute(): float
    {
        return $this->payrollInputs ? $this->payrollInputs->sum('net_pay') : 0;
    }

    public function getTotalDeductionsAttribute(): float
    {
        return $this->payrollInputs ? $this->payrollInputs->sum('deductions') : 0;
    }

    /**
     * Calculate working days between cutoff_start and cutoff_end (excluding weekends)
     */
    public function getWorkingDaysAttribute(): int
    {
        return $this->calculateWorkingDays($this->cutoff_start, $this->cutoff_end);
    }

    /**
     * Calculate working days between two dates (excluding weekends: Saturday and Sunday)
     */
    public static function calculateWorkingDays($startDate, $endDate): int
    {
        $start = $startDate instanceof \DateTime ? $startDate : new \DateTime($startDate);
        $end = $endDate instanceof \DateTime ? $endDate : new \DateTime($endDate);
        
        $workingDays = 0;
        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($start, $interval, $end->modify('+1 day'));
        
        foreach ($period as $day) {
            // Exclude Saturday (6) and Sunday (0)
            if ($day->format('N') != 6 && $day->format('N') != 0) {
                $workingDays++;
            }
        }
        
        return $workingDays;
    }

    /**
     * Check if the payroll period falls in the second half of the month (16-30,31)
     */
    public function isSecondHalfOfMonth(): bool
    {
        return $this->cutoff_start->format('d') >= 16;
    }
}