<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'work_request_id',
        'date',
        'time_in',
        'time_out',
        'rendered_hours',
        'computed_days',
        'remarks',
    ];

    protected $casts = [
        'date' => 'date',
        'rendered_hours' => 'decimal:2',
        'computed_days' => 'decimal:2',
    ];

    /**
     * Relationship: belongs to employee
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relationship: belongs to work request
     */
    public function workRequest(): BelongsTo
    {
        return $this->belongsTo(WorkRequest::class);
    }

    /**
     * Compute rendered hours from time_in and time_out
     * Handles breaks if configured
     */
    public function computeRenderedHours(): float
    {
        if (!$this->time_in || !$this->time_out) {
            return 0;
        }

        try {
            // Parse time strings (format: H:i)
            $timeIn = Carbon::createFromFormat('H:i', $this->time_in);
            $timeOut = Carbon::createFromFormat('H:i', $this->time_out);

            // Calculate total minutes difference
            $totalMinutes = $timeOut->diffInMinutes($timeIn);

            // Ensure we get positive value (time_out should be after time_in)
            if ($totalMinutes < 0) {
                $totalMinutes = abs($totalMinutes);
            }

            // Convert to hours
            $hours = $totalMinutes / 60;

            // Handle break time (e.g., 1 hour break for shifts > 4 hours)
            // This can be configured based on company policy
            if ($hours > 4) {
                $hours -= 1; // Subtract 1 hour break
            }

            return round(max(0, $hours), 2);
        } catch (\Exception $e) {
            \Log::error('computeRenderedHours error', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Compute days from rendered hours
     * Logic:
     * - < 4 hours = 0 days
     * - 4 to < 8 hours = 0.5 days
     * - >= 8 hours = 1 day
     */
    public function computeDays(?float $hours = null): float
    {
        $hours = $hours ?? $this->rendered_hours;

        if ($hours < 4) {
            return 0;
        } elseif ($hours >= 4 && $hours < 8) {
            return 0.5;
        } else {
            return 1;
        }
    }

    /**
     * Auto-compute rendered_hours and computed_days before saving
     * Auto-link to approved work request if exists
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($attendance) {
            if (!empty($attendance->time_in) && !empty($attendance->time_out)) {
                $attendance->rendered_hours = $attendance->computeRenderedHours();
                $attendance->computed_days = $attendance->computeDays($attendance->rendered_hours);
            } else {
                // Set to 0 if times are missing
                $attendance->rendered_hours = 0;
                $attendance->computed_days = 0;
            }

            // Auto-link to approved work request if not already linked
            if (!$attendance->work_request_id) {
                $attendance->linkToApprovedWorkRequest();
            }
        });
    }

    /**
     * Scope: get attendance for a payroll period
     */
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope: get attendance for a specific employee
     */
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Get total computed days for a period
     */
    public static function getTotalDaysForPeriod($employeeId, $startDate, $endDate): float
    {
        return self::forEmployee($employeeId)
            ->forPeriod($startDate, $endDate)
            ->sum('computed_days');
    }

    /**
     * Get total rendered hours for a period
     */
    public static function getTotalHoursForPeriod($employeeId, $startDate, $endDate): float
    {
        return self::forEmployee($employeeId)
            ->forPeriod($startDate, $endDate)
            ->sum('rendered_hours');
    }

    /**
     * Auto-link attendance to approved work request for the same date
     * Returns true if linked, false otherwise
     */
    public function linkToApprovedWorkRequest(): bool
    {
        if ($this->work_request_id) {
            return true; // Already linked
        }

        $approvedRequest = WorkRequest::where('employee_id', $this->employee_id)
            ->where('work_date', $this->date)
            ->where('status', 'approved')
            ->first();

        if ($approvedRequest) {
            $this->work_request_id = $approvedRequest->id;
            $this->save();
            return true;
        }

        return false;
    }

    /**
     * Check if attendance has an approved work request
     */
    public function hasApprovedWorkRequest(): bool
    {
        return $this->workRequest && $this->workRequest->status === 'approved';
    }

    /**
     * Check if attendance is on a weekend without approved request
     */
    public function isUnauthorizedWeekendWork(): bool
    {
        $date = Carbon::parse($this->date);
        return $date->isWeekend() && !$this->hasApprovedWorkRequest();
    }

    /**
     * Check if attendance is on a holiday without approved request
     */
    public function isUnauthorizedHolidayWork(): bool
    {
        return Holiday::isHoliday($this->date) && !$this->hasApprovedWorkRequest();
    }

    /**
     * Check if attendance needs HR review (unauthorized special work)
     */
    public function needsHrReview(): bool
    {
        return $this->isUnauthorizedWeekendWork() || $this->isUnauthorizedHolidayWork();
    }
}
