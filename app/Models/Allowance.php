<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Allowance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'name',
        'amount',
        'type',
        'description',
        'is_active',
        'effective_date',
        'end_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
        'effective_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the employee that owns the allowance.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Scope: Get active allowances
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
