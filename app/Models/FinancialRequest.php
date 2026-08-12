<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'request_type',
        'request_date',
        'amount',
        'description',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'receipt_image',
        'amount_paid',
    ];

    protected $casts = [
        'request_date' => 'date',
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

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
     * Relationship: has many cash advance payments
     */
    public function cashAdvancePayments(): HasMany
    {
        return $this->hasMany(CashAdvancePayment::class);
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
     * Scope: cash advances
     */
    public function scopeCashAdvances($query)
    {
        return $query->where('request_type', 'cash_advance');
    }

    /**
     * Scope: reimbursements
     */
    public function scopeReimbursements($query)
    {
        return $query->where('request_type', 'reimbursement');
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

    /**
     * Get remaining balance for cash advances
     */
    public function getRemainingBalanceAttribute(): float
    {
        if ($this->request_type !== 'cash_advance') {
            return 0;
        }
        return max(0, $this->amount - $this->amount_paid);
    }

    /**
     * Check if cash advance is fully paid
     */
    public function isFullyPaid(): bool
    {
        if ($this->request_type !== 'cash_advance') {
            return false;
        }
        return $this->amount_paid >= $this->amount;
    }

    /**
     * Get total outstanding cash advance balance for an employee
     */
    public static function getOutstandingBalanceForEmployee($employeeId): float
    {
        return self::where('employee_id', $employeeId)
            ->where('request_type', 'cash_advance')
            ->where('status', 'approved')
            ->get()
            ->sum(function($request) {
                return max(0, $request->amount - $request->amount_paid);
            });
    }

    /**
     * Get payment progress percentage
     */
    public function getPaymentProgressAttribute(): float
    {
        if ($this->request_type !== 'cash_advance' || $this->amount == 0) {
            return 0;
        }
        return round(($this->amount_paid / $this->amount) * 100, 1);
    }
}
