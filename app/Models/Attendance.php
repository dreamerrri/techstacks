<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'month',
        'year',
        'days_worked',
        'regular_hours',
        'overtime_hours',
        'late_hours',
        'night_differential_hours',
        'regular_holiday_worked',
    ];

    protected $casts = [
        'days_worked' => 'integer',
        'regular_hours' => 'integer',
        'overtime_hours' => 'integer',
        'late_hours' => 'integer',
        'night_differential_hours' => 'integer',
        'regular_holiday_worked' => 'integer',
    ];

    /**
     * Get the employee that owns the attendance.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Scope: Get attendance for a specific month and year
     */
    public function scopeForMonth($query, $month, $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    /**
     * Scope: Get current month's attendance
     */
    public function scopeCurrentMonth($query)
    {
        return $query->where('month', date('m'))->where('year', date('Y'));
    }
}
