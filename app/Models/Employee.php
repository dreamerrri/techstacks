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

    public function payrollInputs(): HasMany
{
    return $this->hasMany(PayrollInput::class);
}
    


}