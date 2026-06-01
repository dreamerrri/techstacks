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
        'sss_rate',
        'sss_cap',
        'philhealth_rate',
        'philhealth_cap',
        'pagibig_rate',
        'pagibig_cap',
        'is_archived',
    ];

    protected $casts = [
        'birthdate'   => 'date',
        'date_hired'  => 'date',
        'is_archived' => 'boolean',
        'basic_salary'=> 'decimal:2',
        'sss_rate'    => 'decimal:4',
        'sss_cap'     => 'decimal:2',
        'philhealth_rate' => 'decimal:4',
        'philhealth_cap'  => 'decimal:2',
        'pagibig_rate'    => 'decimal:4',
        'pagibig_cap'     => 'decimal:2',
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

    // Relationship: payroll input records
    public function payrollInputs()
    {
        return $this->hasMany(\App\Models\PayrollInput::class);
    }

    // Get current payroll period's payroll input
    public function currentPayrollInput()
    {
        $currentPeriod = \App\Models\PayrollPeriod::where('status', 'draft')
            ->where('cutoff_start', '<=', now())
            ->where('cutoff_end', '>=', now())
            ->first();
        
        if ($currentPeriod) {
            return $this->payrollInputs()->where('payroll_period_id', $currentPeriod->id)->first();
        }
        
        return null;
    }

    // Get most recent payroll input record
    public function latestPayrollInput()
    {
        return $this->payrollInputs()
            ->with('payrollPeriod')
            ->orderByDesc('payroll_period_id')
            ->first();
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