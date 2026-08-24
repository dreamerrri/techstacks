<?php

namespace App\Http\Controllers;

use App\Models\WorkRequest;
use App\Models\Employee;
use App\Models\Holiday;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class WorkRequestController extends Controller
{
    /**
     * GET /work-requests
     * Show work requests (employee sees own, HR/Admin sees all)
     */
    public function index(Request $request)
    {
        $user    = Auth::user();

        $admin = $user->isAdmin();
        $hr   = $user->isHR();

        $employee = $user->employee;

        // Filter by status if provided
        $status = $request->input('status');
        $type = $request->input('type');

        if ($admin || $hr) {
            // HR/Admin can see all work requests (no employee record needed)
            $query = WorkRequest::with(['employee', 'approvedBy']);
        } else {
            // Employees must have an employee record to view their own requests
            if (!$employee) {
                abort(403, 'No employee record found for this user.');
            }
            // Employees can only see their own work requests
            $query = $employee->workRequests()->with(['approvedBy']);
        }

        // Apply filters
        if ($status) {
            $query->where('status', $status);
        }
        if ($type) {
            $query->where('request_type', $type);
        }

        $workRequests = $query->orderBy('created_at', 'desc')->get();

        // Get pending count for HR/Admin
        $pendingCount = 0;
        if ($admin || $hr) {
            $pendingCount = WorkRequest::pending()->count();
        }

        return Inertia::render('WorkRequests/Index', [
            'workRequests' => $workRequests,
            'pendingCount' => $pendingCount,
            'status' => $status,
            'type' => $type,
        ]);
    }

    /**
     * GET /work-requests/create
     * Show form to create work request
     */
    public function create()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            abort(403, 'No employee record found for this user.');
        }

        // Get upcoming holidays for reference
        $upcomingHolidays = Holiday::getUpcomingHolidays(10);

        return Inertia::render('WorkRequests/Create', [
            'employee' => $employee,
            'upcomingHolidays' => $upcomingHolidays,
        ]);
    }

    /**
     * POST /work-requests
     * Store new work request
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->with('error', 'No employee record found for this user.');
        }

        $validated = $request->validate([
            'request_type' => 'required|in:weekend,holiday,overtime,half_day',
            'work_date' => 'required|date|after_or_equal:today',
            'estimated_hours' => 'nullable|numeric|min:0|max:24',
            'reason' => 'nullable|string|max:500',
        ]);

        // Handle time fields separately - pass directly to model without validation
        $validated['start_time'] = $request->input('start_time');
        $validated['end_time'] = $request->input('end_time');

        // Calculate overtime hours for overtime requests
        $calculatedOvertimeHours = null;
        if ($validated['request_type'] === 'overtime' && !empty($validated['start_time']) && !empty($validated['end_time'])) {
            $calculatedOvertimeHours = $this->calculateOvertimeHours($validated['start_time'], $validated['end_time']);
        }

        // Check if there's already a pending request for this date
        $existingRequest = WorkRequest::where('employee_id', $employee->id)
            ->where('work_date', $validated['work_date'])
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingRequest) {
            return back()->with('error', 'You already have a pending or approved request for this date.');
        }

        $workRequest = WorkRequest::create([
            'employee_id' => $employee->id,
            'request_type' => $validated['request_type'],
            'work_date' => $validated['work_date'],
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'estimated_hours' => $validated['estimated_hours'] ?? null,
            'calculated_overtime_hours' => $calculatedOvertimeHours,
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);

        // Notify HR/Admin about the new work request
        NotificationService::notifyWorkRequestSubmitted($employee, $workRequest);

        return redirect()->route('work-requests.index')->with('success', 'Work request submitted successfully.');
    }

    /**
     * GET /work-requests/{workRequest}
     * Show work request details
     */
    public function show(WorkRequest $workRequest)
    {
        $user    = Auth::user();

        $admin = $user->isAdmin();
        $hr   = $user->isHR();

        $employee = $user->employee;

        // HR/Admin can view all work requests (no employee record needed)
        if (!$admin && !$hr) {
            // Employees must have an employee record to view their own requests
            if (!$employee) {
                abort(403, 'No employee record found for this user.');
            }
            // Employees can only view their own requests
            if ($workRequest->employee_id !== $employee->id) {
                abort(403, 'You can only view your own work requests.');
            }
        }

        $workRequest->load(['employee', 'approvedBy']);

        return Inertia::render('WorkRequests/Show', [
            'workRequest' => $workRequest,
        ]);
    }

    /**
     * GET /work-requests/{workRequest}/edit
     * Show form to edit work request (only pending requests)
     * Only employees can edit their own requests
     */
    public function edit(WorkRequest $workRequest)
    {
        $user    = Auth::user();

        $admin = $user->isAdmin();
        $hr   = $user->isHR();

        $employee = $user->employee;

        // HR/Admin cannot edit employee requests
        if ($admin || $hr) {
            abort(403, 'HR/Admin cannot edit employee work requests. Use approve/reject instead.');
        }

        if (!$employee) {
            abort(403, 'No employee record found for this user.');
        }

        // Employees can only edit their own pending requests
        if ($workRequest->employee_id !== $employee->id) {
            abort(403, 'You can only edit your own work requests.');
        }
        if (!$workRequest->canBeCancelled()) {
            abort(403, 'You can only edit pending requests.');
        }

        $upcomingHolidays = Holiday::getUpcomingHolidays(10);

        return Inertia::render('WorkRequests/Edit', [
            'workRequest' => $workRequest,
            'upcomingHolidays' => $upcomingHolidays,
        ]);
    }

    /**
     * PUT /work-requests/{workRequest}
     * Update work request
     * Only employees can update their own requests
     */
    public function update(Request $request, WorkRequest $workRequest)
    {
        $user    = Auth::user();

        $admin = $user->isAdmin();
        $hr   = $user->isHR();

        $employee = $user->employee;

        // HR/Admin cannot update employee requests
        if ($admin || $hr) {
            return back()->with('error', 'HR/Admin cannot edit employee work requests. Use approve/reject instead.');
        }

        if (!$employee) {
            return back()->with('error', 'No employee record found for this user.');
        }

        // Employees can only update their own pending requests
        if ($workRequest->employee_id !== $employee->id) {
            return back()->with('error', 'You can only edit your own work requests.');
        }
        if (!$workRequest->canBeCancelled()) {
            return back()->with('error', 'You can only edit pending requests.');
        }

        $validated = $request->validate([
            'request_type' => 'required|in:weekend,holiday,overtime,half_day',
            'work_date' => 'required|date|after_or_equal:today',
            'estimated_hours' => 'nullable|numeric|min:0|max:24',
            'reason' => 'nullable|string|max:500',
        ]);

        // Handle time fields separately - pass directly to model without validation
        $validated['start_time'] = $request->input('start_time');
        $validated['end_time'] = $request->input('end_time');

        // Calculate overtime hours for overtime requests
        $calculatedOvertimeHours = null;
        if ($validated['request_type'] === 'overtime' && !empty($validated['start_time']) && !empty($validated['end_time'])) {
            $calculatedOvertimeHours = $this->calculateOvertimeHours($validated['start_time'], $validated['end_time']);
        }

        $workRequest->update([
            'request_type' => $validated['request_type'],
            'work_date' => $validated['work_date'],
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'estimated_hours' => $validated['estimated_hours'] ?? null,
            'calculated_overtime_hours' => $calculatedOvertimeHours,
            'reason' => $validated['reason'] ?? null,
        ]);

        return redirect()->route('work-requests.show', $workRequest)->with('success', 'Work request updated successfully.');
    }

    /**
     * DELETE /work-requests/{workRequest}
     * Cancel work request (only pending requests)
     */
    public function destroy(WorkRequest $workRequest)
    {
        $user    = Auth::user();

        $admin = $user->isAdmin();
        $hr   = $user->isHR();

        $employee = $user->employee;

        if (!$employee) {
            return back()->with('error', 'No employee record found for this user.');
        }

        // Employees can only cancel their own pending requests
        if (!$admin && !$hr) {
            if ($workRequest->employee_id !== $employee->id) {
                return back()->with('error', 'You can only cancel your own work requests.');
            }
        }

        // Everyone (including HR/Admin) can only cancel pending requests,
        // so approved/rejected requests can't bypass the state machine
        if (!$workRequest->canBeCancelled()) {
            return back()->with('error', 'You can only cancel pending requests.');
        }

        $workRequest->update(['status' => 'cancelled']);

        // Notify employee about cancellation
        NotificationService::notifyWorkRequestCancelled($workRequest->employee, $workRequest);

        return redirect()->route('work-requests.index')->with('success', 'Work request cancelled successfully.');
    }

    /**
     * GET /work-requests/pending
     * Show pending work requests (HR/Admin only)
     */
    public function pending()
    {
        $user    = Auth::user();

        $admin = $user->isAdmin();
        $hr   = $user->isHR();

        if (!$admin && !$hr) {
            abort(403, 'Only administrators and HR can view pending requests.');
        }

        $pendingRequests = WorkRequest::pending()
            ->with(['employee'])
            ->orderBy('work_date', 'asc')
            ->get();

        return Inertia::render('WorkRequests/Pending', [
            'pendingRequests' => $pendingRequests,
        ]);
    }

    /**
     * POST /work-requests/{workRequest}/approve
     * Approve work request (HR/Admin only)
     */
    public function approve(Request $request, WorkRequest $workRequest)
    {
        $user    = Auth::user();

        $admin = $user->isAdmin();
        $hr   = $user->isHR();

        if (!$admin && !$hr) {
            return back()->with('error', 'Only administrators and HR can approve requests.');
        }

        // Separation of duties: no approving your own request
        if ($workRequest->employee?->user_id === $user->id) {
            return back()->with('error', 'You cannot approve your own work request.');
        }

        if (!$workRequest->canBeApproved()) {
            return back()->with('error', 'This request cannot be approved.');
        }

        $workRequest->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        // Notify employee about approval
        NotificationService::notifyWorkRequestApproved($workRequest->employee, $workRequest);

        return back()->with('success', 'Work request approved successfully.');
    }

    /**
     * POST /work-requests/{workRequest}/reject
     * Reject work request (HR/Admin only)
     */
    public function reject(Request $request, WorkRequest $workRequest)
    {
        $user    = Auth::user();

        $admin = $user->isAdmin();
        $hr   = $user->isHR();

        if (!$admin && !$hr) {
            return back()->with('error', 'Only administrators and HR can reject requests.');
        }

        // Separation of duties: no rejecting your own request
        if ($workRequest->employee?->user_id === $user->id) {
            return back()->with('error', 'You cannot reject your own work request.');
        }

        if (!$workRequest->canBeRejected()) {
            return back()->with('error', 'This request cannot be rejected.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $workRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        // Notify employee about rejection
        NotificationService::notifyWorkRequestRejected($workRequest->employee, $workRequest, $validated['rejection_reason']);

        return back()->with('success', 'Work request rejected successfully.');
    }

    /**
     * Calculate overtime hours from start and end time
     * Overtime = total hours worked - 8 regular hours
     * Handles overnight shifts (e.g., 22:00 to 02:00)
     */
    private function calculateOvertimeHours(string $startTime, string $endTime): ?float
    {
        if (empty($startTime) || empty($endTime)) {
            return null;
        }

        $startParts = explode(':', $startTime);
        $endParts = explode(':', $endTime);

        $startMinutes = intval($startParts[0]) * 60 + intval($startParts[1]);
        $endMinutes = intval($endParts[0]) * 60 + intval($endParts[1]);

        $totalMinutes = $endMinutes - $startMinutes;

        // Handle overnight work (e.g., 22:00 to 02:00)
        if ($totalMinutes < 0) {
            $totalMinutes += 24 * 60;
        }

        $totalHours = $totalMinutes / 60;

        // Overtime is hours worked beyond regular 8 hours
        $regularHours = 8;
        $overtimeHours = $totalHours - $regularHours;

        if ($overtimeHours < 0) {
            $overtimeHours = 0;
        }

        // Round to 2 decimal places
        return round($overtimeHours, 2);
    }
}
