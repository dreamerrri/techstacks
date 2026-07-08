<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'request_type',
        'work_date',
        'start_time',
        'end_time',
        'estimated_hours',
        'calculated_overtime_hours',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'work_date' => 'date',
        'estimated_hours' => 'decimal:2',
        'calculated_overtime_hours' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Set start_time attribute - convert H:i to H:i:s for TIME column
     */
    public function setStartTimeAttribute($value)
    {
        if (empty($value) || trim($value) === '') {
            $this->attributes['start_time'] = null;
        } else {
            // Check if value already has seconds (H:i:s format)
            if (substr_count($value, ':') === 2) {
                $this->attributes['start_time'] = $value;
            } else {
                // Convert H:i to H:i:s format for MySQL TIME column
                $this->attributes['start_time'] = $value . ':00';
            }
        }
    }

    /**
     * Set end_time attribute - convert H:i to H:i:s for TIME column
     */
    public function setEndTimeAttribute($value)
    {
        if (empty($value) || trim($value) === '') {
            $this->attributes['end_time'] = null;
        } else {
            // Check if value already has seconds (H:i:s format)
            if (substr_count($value, ':') === 2) {
                $this->attributes['end_time'] = $value;
            } else {
                // Convert H:i to H:i:s format for MySQL TIME column
                $this->attributes['end_time'] = $value . ':00';
            }
        }
    }

    /**
     * Relationship: belongs to employee
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relationship: belongs to user who approved
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relationship: has many attendances
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Scope: pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope: approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope: cancelled requests
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope: by request type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('request_type', $type);
    }

    /**
     * Scope: for a specific employee
     */
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope: for a date range
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('work_date', [$startDate, $endDate]);
    }

    /**
     * Check if request can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request can be approved
     */
    public function canBeApproved(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if request can be rejected
     */
    public function canBeRejected(): bool
    {
        return $this->status === 'pending';
    }
}
