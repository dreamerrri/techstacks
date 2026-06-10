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

    // ── Phase Detection ────────────────────────────────────────

    /**
     * Detect phase from cutoff_start day.
     * Phase 1 = 1st–15th, Phase 2 = 16th–end of month.
     */
    public function getPhaseAttribute(): int
    {
        return $this->cutoff_start->day <= 15 ? 1 : 2;
    }

    /**
     * Human-readable phase label: "1st Half" or "2nd Half"
     */
    public function getPhaseLabelAttribute(): string
    {
        return $this->phase === 1 ? '1st Half' : '2nd Half';
    }

    /**
     * Full period label, e.g. "June 2025 – 1st Half"
     */
    public function getPeriodLabelAttribute(): string
    {
        return $this->cutoff_start->format('F Y') . ' – ' . $this->phase_label;
    }

    /**
     * Short period label, e.g. "Jun 2025 · P1"
     */
    public function getShortLabelAttribute(): string
    {
        return $this->cutoff_start->format('M Y') . ' · P' . $this->phase;
    }

    // ── Guards ─────────────────────────────────────────────────

    /**
     * Check if a payroll period already exists for the given month/year and phase.
     * Used to prevent duplicate phase entries per month.
     */
    public static function existsForMonthAndPhase(int $year, int $month, int $phase): bool
    {
        return self::whereYear('cutoff_start', $year)
            ->whereMonth('cutoff_start', $month)
            ->where(function ($q) use ($phase) {
                if ($phase === 1) {
                    $q->whereDay('cutoff_start', '<=', 15);
                } else {
                    $q->whereDay('cutoff_start', '>', 15);
                }
            })
            ->exists();
    }

    /**
     * Check if the 15-period cap has been reached.
     */
    public static function isAtCapacity(int $cap = 15): bool
    {
        return self::count() >= $cap;
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
     * Calculate working days between cutoff_start and cutoff_end (excluding weekends).
     */
    public function getWorkingDaysAttribute(): int
    {
        return $this->calculateWorkingDays($this->cutoff_start, $this->cutoff_end);
    }

    /**
     * Calculate working days between two dates (excluding weekends: Saturday and Sunday).
     */
    public static function calculateWorkingDays($startDate, $endDate): int
    {
        $start = $startDate instanceof \DateTime ? $startDate : new \DateTime($startDate);
        $end   = $endDate instanceof \DateTime ? $endDate : new \DateTime($endDate);

        $workingDays = 0;
        $interval    = new \DateInterval('P1D');
        $period      = new \DatePeriod($start, $interval, $end->modify('+1 day'));

        foreach ($period as $day) {
            // Exclude Saturday (6) and Sunday (7 in ISO-8601, not 0)
            if ($day->format('N') != 6 && $day->format('N') != 7) {
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