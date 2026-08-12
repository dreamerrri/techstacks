<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashAdvancePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'financial_request_id',
        'payroll_period_id',
        'amount',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Relationship: belongs to financial request
     */
    public function financialRequest(): BelongsTo
    {
        return $this->belongsTo(FinancialRequest::class);
    }

    /**
     * Relationship: belongs to payroll period
     */
    public function payrollPeriod(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class);
    }
}
