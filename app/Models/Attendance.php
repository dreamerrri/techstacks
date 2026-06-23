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
     * Compute rendered hours from time_in and time_out
     * Handles breaks if configured
     */
    public function computeRenderedHours(): float
    {
        \Log::info('computeRenderedHours called', [
            'time_in' => $this->time_in,
            'time_out' => $this->time_out,
            'time_in_type' => gettype($this->time_in),
            'time_out_type' => gettype($this->time_out),
        ]);

        if (!$this->time_in || !$this->time_out) {
            \Log::info('computeRenderedHours: times missing, returning 0');
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

            \Log::info('computeRenderedHours: calculated hours', ['hours' => $hours]);

            // Handle break time (e.g., 1 hour break for shifts > 4 hours)
            // This can be configured based on company policy
            if ($hours > 4) {
                $hours -= 1; // Subtract 1 hour break
            }

            $result = round(max(0, $hours), 2);
            \Log::info('computeRenderedHours: result', ['result' => $result]);
            return $result;
        } catch (\Exception $e) {
            \Log::error('computeRenderedHours error', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Compute days from rendered hours
     * Logic:
     * - < 4 hours = 0 days
     * - 4-8 hours = 0.5 days
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
}
