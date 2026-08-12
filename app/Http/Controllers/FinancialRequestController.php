<?php

namespace App\Http\Controllers;

use App\Models\FinancialRequest;
use App\Models\Employee;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FinancialRequestController extends Controller
{
    /**
     * GET /financial-requests
     * Show financial requests (employee sees own, HR/Admin sees all)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $admin = $user->isAdmin();
        $hr = $user->isHR();
        $employee = $user->employee;

        // Filter by status if provided
        $status = $request->input('status');
        $type = $request->input('type');

        if ($admin || $hr) {
            // HR/Admin can see all financial requests
            $query = FinancialRequest::with(['employee', 'approvedBy']);
        } else {
            // Employees must have an employee record to view their own requests
            if (!$employee) {
                abort(403, 'No employee record found for this user.');
            }
            // Employees can only see their own financial requests
            $query = $employee->financialRequests()->with(['approvedBy']);
        }

        // Apply filters
        if ($status) {
            $query->where('status', $status);
        }
        if ($type) {
            $query->where('request_type', $type);
        }

        $financialRequests = $query->orderBy('created_at', 'desc')->get();

        // Get pending count for HR/Admin
        $pendingCount = 0;
        if ($admin || $hr) {
            $pendingCount = FinancialRequest::pending()->count();
        }

        return view('financial-requests.index', compact(
            'financialRequests',
            'pendingCount',
            'status',
            'type'
        ));
    }

    /**
     * GET /financial-requests/create
     * Show form to create financial request
     */
    public function create()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'No employee record found for this user.');
        }

        return view('financial-requests.create', compact('employee'));
    }

    /**
     * POST /financial-requests
     * Store new financial request
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'No employee record found for this user.',
            ], 403);
        }

        $validated = $request->validate([
            'request_type' => 'required|in:cash_advance,reimbursement',
            'amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:1000',
            'receipt_image' => 'nullable|image|max:2048', // 2MB max
        ]);

        // Set amount based on request type
        if ($validated['request_type'] === 'cash_advance') {
            // Cash advance is automatically set to basic salary
            $validated['amount'] = $employee->basic_salary;
        } else {
            // Reimbursement amount must be provided and validated
            if (empty($validated['amount'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Amount is required for reimbursement requests.',
                ], 422);
            }
            
            // Reimbursement limited to ₱15,000
            $maxAmount = 15000;
            if ($validated['amount'] > $maxAmount) {
                return response()->json([
                    'success' => false,
                    'message' => "Amount cannot exceed ₱" . number_format($maxAmount, 2) . " (₱15,000 maximum).",
                ], 422);
            }
        }

        // Handle receipt image upload for reimbursements
        $receiptImagePath = null;
        if ($request->hasFile('receipt_image') && $validated['request_type'] === 'reimbursement') {
            $receiptImagePath = $request->file('receipt_image')->store('receipts', 'public');
        }

        $financialRequest = FinancialRequest::create([
            'employee_id' => $employee->id,
            'request_type' => $validated['request_type'],
            'request_date' => now(),
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'receipt_image' => $receiptImagePath,
            'status' => 'pending',
        ]);

        // Notify HR/Admin about the new financial request
        NotificationService::notifyFinancialRequestSubmitted($employee, $financialRequest);

        return response()->json([
            'success' => true,
            'message' => 'Financial request submitted successfully.',
            'financial_request' => $financialRequest,
        ]);
    }

    /**
     * GET /financial-requests/{financialRequest}
     * Show financial request details
     */
    public function show(FinancialRequest $financialRequest)
    {
        $user = Auth::user();
        $admin = $user->isAdmin();
        $hr = $user->isHR();
        $employee = $user->employee;

        // HR/Admin can view all financial requests
        if (!$admin && !$hr) {
            // Employees must have an employee record to view their own requests
            if (!$employee) {
                abort(403, 'No employee record found for this user.');
            }
            // Employees can only view their own requests
            if ($financialRequest->employee_id !== $employee->id) {
                abort(403, 'You can only view your own financial requests.');
            }
        }

        $financialRequest->load(['employee', 'approvedBy', 'cashAdvancePayments.payrollPeriod']);

        return view('financial-requests.show', compact('financialRequest'));
    }

    /**
     * GET /financial-requests/{financialRequest}/edit
     * Show form to edit financial request (only pending requests)
     * Only employees can edit their own requests
     */
    public function edit(FinancialRequest $financialRequest)
    {
        $user = Auth::user();
        $admin = $user->isAdmin();
        $hr = $user->isHR();
        $employee = $user->employee;

        // HR/Admin cannot edit employee requests
        if ($admin || $hr) {
            abort(403, 'HR/Admin cannot edit employee financial requests. Use approve/reject instead.');
        }

        if (!$employee) {
            abort(403, 'No employee record found for this user.');
        }

        // Employees can only edit their own pending requests
        if ($financialRequest->employee_id !== $employee->id) {
            abort(403, 'You can only edit your own financial requests.');
        }
        if (!$financialRequest->canBeCancelled()) {
            abort(403, 'You can only edit pending requests.');
        }

        return view('financial-requests.edit', compact('financialRequest'));
    }

    /**
     * PUT/PATCH /financial-requests/{financialRequest}
     * Update financial request
     */
    public function update(Request $request, FinancialRequest $financialRequest): JsonResponse
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'No employee record found for this user.',
            ], 403);
        }

        // Employees can only update their own pending requests
        if ($financialRequest->employee_id !== $employee->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only update your own financial requests.',
            ], 403);
        }
        if (!$financialRequest->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only update pending requests.',
            ], 403);
        }

        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:1000',
            'receipt_image' => 'nullable|image|max:2048',
        ]);

        // Handle amount based on request type
        if ($financialRequest->request_type === 'cash_advance') {
            // Cash advance amount remains fixed to basic salary
            $validated['amount'] = $employee->basic_salary;
        } else {
            // Reimbursement amount must be provided and validated
            if (empty($validated['amount'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Amount is required for reimbursement requests.',
                ], 422);
            }
            
            // Reimbursement limited to ₱15,000
            $maxAmount = 15000;
            if ($validated['amount'] > $maxAmount) {
                return response()->json([
                    'success' => false,
                    'message' => "Amount cannot exceed ₱" . number_format($maxAmount, 2) . " (₱15,000 maximum).",
                ], 422);
            }
        }

        // Handle receipt image upload for reimbursements
        if ($request->hasFile('receipt_image') && $financialRequest->request_type === 'reimbursement') {
            // Delete old receipt if exists
            if ($financialRequest->receipt_image) {
                Storage::disk('public')->delete($financialRequest->receipt_image);
            }
            $validated['receipt_image'] = $request->file('receipt_image')->store('receipts', 'public');
        }

        $financialRequest->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Financial request updated successfully.',
            'financial_request' => $financialRequest,
        ]);
    }

    /**
     * DELETE /financial-requests/{financialRequest}
     * Cancel financial request (only pending requests)
     */
    public function destroy(FinancialRequest $financialRequest): JsonResponse
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'No employee record found for this user.',
            ], 403);
        }

        // Employees can only cancel their own pending requests
        if ($financialRequest->employee_id !== $employee->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only cancel your own financial requests.',
            ], 403);
        }
        if (!$financialRequest->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only cancel pending requests.',
            ], 403);
        }

        $financialRequest->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Financial request cancelled successfully.',
        ]);
    }

    /**
     * POST /financial-requests/{financialRequest}/approve
     * Approve financial request (HR/Admin only)
     */
    public function approve(Request $request, FinancialRequest $financialRequest): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isHR()) {
            return response()->json([
                'success' => false,
                'message' => 'Only HR and Admin can approve financial requests.',
            ], 403);
        }

        if (!$financialRequest->canBeApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'This request cannot be approved.',
            ], 403);
        }

        $financialRequest->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        // Notify employee about approval
        NotificationService::notifyFinancialRequestApproved($financialRequest->employee, $financialRequest);

        return response()->json([
            'success' => true,
            'message' => 'Financial request approved successfully.',
            'financial_request' => $financialRequest,
        ]);
    }

    /**
     * POST /financial-requests/{financialRequest}/reject
     * Reject financial request (HR/Admin only)
     */
    public function reject(Request $request, FinancialRequest $financialRequest): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isAdmin() && !$user->isHR()) {
            return response()->json([
                'success' => false,
                'message' => 'Only HR and Admin can reject financial requests.',
            ], 403);
        }

        if (!$financialRequest->canBeRejected()) {
            return response()->json([
                'success' => false,
                'message' => 'This request cannot be rejected.',
            ], 403);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $financialRequest->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        // Notify employee about rejection
        NotificationService::notifyFinancialRequestRejected($financialRequest->employee, $financialRequest);

        return response()->json([
            'success' => true,
            'message' => 'Financial request rejected successfully.',
            'financial_request' => $financialRequest,
        ]);
    }
}
