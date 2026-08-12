<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Employee;
use App\Models\Allowance;
use App\Models\Benefit;
use App\Models\PayrollPeriod;
use Carbon\Carbon;

class NotificationService
{
    public static function generateHrAdminNotifications(): void
    {
        // 1. Unassigned department/position
        $unassigned = Employee::active()
            ->where(function($q) {
                $q->where('department', 'Unassigned')
                  ->orWhere('position', 'Unassigned');
            })->get();

        foreach ($unassigned as $emp) {
            $existing = Notification::where('type', 'unassigned_employee')
                ->where('data->employee_id', $emp->id)
                ->where('is_resolved', false)
                ->first();

            if (!$existing) {
                Notification::createForHrAdmin([
                    'title' => 'Unassigned Employee',
                    'message' => "{$emp->full_name} needs department/position assignment",
                    'type' => 'alert',
                    'link' => route('employees.edit', $emp, false),
                    'data' => ['employee_id' => $emp->id],
                ]);
            }
        }

        // 2. Missing government IDs
        $missingGovIds = Employee::active()
            ->where(function($q) {
                $q->whereNull('sss_number')
                  ->orWhereNull('philhealth_number')
                  ->orWhereNull('pagibig_number')
                  ->orWhereNull('tin_number');
            })->get();

        foreach ($missingGovIds as $emp) {
            $missing = collect(['SSS' => $emp->sss_number, 'PhilHealth' => $emp->philhealth_number, 'Pag-IBIG' => $emp->pagibig_number, 'TIN' => $emp->tin_number])
                ->filter(fn($v) => is_null($v))
                ->keys()
                ->implode(', ');

            $existing = Notification::where('type', 'missing_gov_ids')
                ->where('data->employee_id', $emp->id)
                ->where('is_resolved', false)
                ->first();

            if (!$existing) {
                Notification::createForHrAdmin([
                    'title' => 'Missing Government IDs',
                    'message' => "{$emp->full_name} missing: {$missing}",
                    'type' => 'warning',
                    'link' => route('employees.edit', $emp, false),
                    'data' => ['employee_id' => $emp->id],
                ]);
            }
        }

        // 3. Overdue payrolls
        $overduePayrolls = PayrollPeriod::where('status', 'draft')
            ->where('payroll_date', '<', now()->toDateString())
            ->get();

        foreach ($overduePayrolls as $period) {
            $existing = Notification::where('type', 'overdue_payroll')
                ->where('data->payroll_period_id', $period->id)
                ->where('is_resolved', false)
                ->first();

            if (!$existing) {
                Notification::createForHrAdmin([
                    'title' => 'Overdue Payroll',
                    'message' => Carbon::parse($period->cutoff_start)->format('M d') . ' – ' . Carbon::parse($period->cutoff_end)->format('M d, Y') . ' due ' . Carbon::parse($period->payroll_date)->format('M d, Y'),
                    'type' => 'error',
                    'link' => route('payroll.index', [], false),
                    'data' => ['payroll_period_id' => $period->id],
                ]);
            }
        }

        // 4. Expiring allowances
        $expiringAllowances = Allowance::where('is_active', 1)
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->with('employee')
            ->get();

        foreach ($expiringAllowances as $allowance) {
            $existing = Notification::where('type', 'expiring_allowance')
                ->where('data->allowance_id', $allowance->id)
                ->where('is_resolved', false)
                ->first();

            if (!$existing) {
                Notification::createForHrAdmin([
                    'title' => 'Expiring Allowance',
                    'message' => "{$allowance->employee->full_name}'s \"{$allowance->name}\" expires " . Carbon::parse($allowance->end_date)->format('M d, Y'),
                    'type' => 'warning',
                    'link' => route('employees.show', $allowance->employee, false),
                    'data' => ['allowance_id' => $allowance->id],
                ]);
            }
        }

        // 5. Expiring benefits
        $expiringBenefits = Benefit::where('is_active', 1)
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->with('employee')
            ->get();

        foreach ($expiringBenefits as $benefit) {
            $existing = Notification::where('type', 'expiring_benefit')
                ->where('data->benefit_id', $benefit->id)
                ->where('is_resolved', false)
                ->first();

            if (!$existing) {
                Notification::createForHrAdmin([
                    'title' => 'Expiring Benefit',
                    'message' => "{$benefit->employee->full_name}'s \"{$benefit->name}\" expires " . Carbon::parse($benefit->end_date)->format('M d, Y'),
                    'type' => 'warning',
                    'link' => route('employees.show', $benefit->employee, false),
                    'data' => ['benefit_id' => $benefit->id],
                ]);
            }
        }
    }

    public static function notifyWorkRequestSubmitted($employee, $workRequest): void
    {
        // Notify HR/Admin
        Notification::createForHrAdmin([
            'title' => 'New Work Request Submitted',
            'message' => "{$employee->full_name} submitted a {$workRequest->request_type} request for " . Carbon::parse($workRequest->work_date)->format('M d, Y'),
            'type' => 'info',
            'link' => route('work-requests.pending', [], false),
            'data' => ['work_request_id' => $workRequest->id, 'employee_id' => $employee->id],
        ]);

        // Notify employee (confirmation)
        Notification::createForEmployee([
            'title' => 'Work Request Submitted',
            'message' => "Your {$workRequest->request_type} request for " . Carbon::parse($workRequest->work_date)->format('M d, Y') . " has been submitted and is pending approval",
            'type' => 'success',
            'link' => route('work-requests.index', [], false),
            'user_id' => $employee->user_id,
            'data' => ['work_request_id' => $workRequest->id],
        ]);
    }

    public static function notifyWorkRequestApproved($employee, $workRequest): void
    {
        Notification::createForEmployee([
            'title' => 'Work Request Approved',
            'message' => "Your {$workRequest->request_type} request for " . Carbon::parse($workRequest->work_date)->format('M d, Y') . " has been approved",
            'type' => 'success',
            'link' => route('work-requests.index', [], false),
            'user_id' => $employee->user_id,
            'data' => ['work_request_id' => $workRequest->id],
        ]);
    }

    public static function notifyWorkRequestRejected($employee, $workRequest, $reason): void
    {
        Notification::createForEmployee([
            'title' => 'Work Request Rejected',
            'message' => "Your {$workRequest->request_type} request for " . Carbon::parse($workRequest->work_date)->format('M d, Y') . " was rejected: {$reason}",
            'type' => 'error',
            'link' => route('work-requests.index', [], false),
            'user_id' => $employee->user_id,
            'data' => ['work_request_id' => $workRequest->id, 'rejection_reason' => $reason],
        ]);
    }

    public static function notifyWorkRequestCancelled($employee, $workRequest): void
    {
        Notification::createForEmployee([
            'title' => 'Work Request Cancelled',
            'message' => "Your {$workRequest->request_type} request for " . Carbon::parse($workRequest->work_date)->format('M d, Y') . " has been cancelled",
            'type' => 'info',
            'link' => route('work-requests.index', [], false),
            'user_id' => $employee->user_id,
            'data' => ['work_request_id' => $workRequest->id],
        ]);
    }

    public static function notifyUpcomingWork($employee, $workRequest): void
    {
        Notification::createForEmployee([
            'title' => 'Upcoming Work Reminder',
            'message' => "Reminder: You have approved {$workRequest->request_type} work scheduled for " . Carbon::parse($workRequest->work_date)->format('M d, Y') . " at {$workRequest->start_time}",
            'type' => 'info',
            'link' => route('work-requests.index', [], false),
            'user_id' => $employee->user_id,
            'data' => ['work_request_id' => $workRequest->id],
        ]);
    }

    public static function notifyFinancialRequestSubmitted($employee, $financialRequest): void
    {
        // Notify HR/Admin
        Notification::createForHrAdmin([
            'title' => 'New Financial Request Submitted',
            'message' => "{$employee->full_name} submitted a {$financialRequest->request_type} request for ₱" . number_format($financialRequest->amount, 2),
            'type' => 'info',
            'link' => route('financial-requests.index', [], false),
            'data' => ['financial_request_id' => $financialRequest->id, 'employee_id' => $employee->id],
        ]);

        // Notify employee (confirmation)
        Notification::createForEmployee([
            'title' => 'Financial Request Submitted',
            'message' => "Your {$financialRequest->request_type} request for ₱" . number_format($financialRequest->amount, 2) . " has been submitted and is pending approval",
            'type' => 'success',
            'link' => route('financial-requests.index', [], false),
            'user_id' => $employee->user_id,
            'data' => ['financial_request_id' => $financialRequest->id],
        ]);
    }

    public static function notifyFinancialRequestApproved($employee, $financialRequest): void
    {
        Notification::createForEmployee([
            'title' => 'Financial Request Approved',
            'message' => "Your {$financialRequest->request_type} request for ₱" . number_format($financialRequest->amount, 2) . " has been approved",
            'type' => 'success',
            'link' => route('financial-requests.index', [], false),
            'user_id' => $employee->user_id,
            'data' => ['financial_request_id' => $financialRequest->id],
        ]);
    }

    public static function notifyFinancialRequestRejected($employee, $financialRequest): void
    {
        $reason = $financialRequest->rejection_reason ?? 'No reason provided';
        Notification::createForEmployee([
            'title' => 'Financial Request Rejected',
            'message' => "Your {$financialRequest->request_type} request for ₱" . number_format($financialRequest->amount, 2) . " was rejected: {$reason}",
            'type' => 'error',
            'link' => route('financial-requests.index', [], false),
            'user_id' => $employee->user_id,
            'data' => ['financial_request_id' => $financialRequest->id, 'rejection_reason' => $reason],
        ]);
    }
}
