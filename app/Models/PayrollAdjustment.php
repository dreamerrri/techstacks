<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollAdjustment extends Model
{
    protected $fillable = [
        'payroll_input_id',
        'adjustment_type',
        'amount',
        'remarks',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function payrollInput(): BelongsTo
    {
        return $this->belongsTo(PayrollInput::class);
    }
}