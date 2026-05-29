<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',  
        'employee_id',
        'first_name',
        'middle_name',
        'last_name',
        'birthdate',
        'gender',
        'civil_status',
        'address',
        'contact_number',
        'email',
        'department',
        'position',
        'employment_status',
        'date_hired',
        'salary_type',
        'basic_salary',
        'sss_number',
        'philhealth_number',
        'pagibig_number',
        'tin_number',
        'is_archived',
    ];

    protected $casts = [
        'birthdate'   => 'date',
        'date_hired'  => 'date',
        'is_archived' => 'boolean',
        'basic_salary'=> 'decimal:2',
    ];

    // Full name accessor
    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    // Scope: active only
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    // Scope: archived only
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    // Relationship: attendance records
    public function attendances()
    {
        return $this->hasMany(\App\Models\Attendance::class);
    }

    // Get current month's attendance
    public function currentAttendance()
    {
        return $this->attendances()->currentMonth()->first();
    }

    // Get most recent attendance record
    public function latestAttendance()
    {
        return $this->attendances()->orderBy('year', 'desc')->orderBy('month', 'desc')->first();
    }

    // Relationship: allowances
    public function allowances()
    {
        return $this->hasMany(\App\Models\Allowance::class);
    }

    // Relationship: benefits
    public function benefits()
    {
        return $this->hasMany(\App\Models\Benefit::class);
    }

    // Get active allowances
    public function activeAllowances()
    {
        return $this->allowances()->active();
    }

    // Get active benefits
    public function activeBenefits()
    {
        return $this->benefits()->active();
    }


}