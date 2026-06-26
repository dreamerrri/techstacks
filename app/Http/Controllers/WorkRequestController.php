<?php

namespace App\Http\Controllers;

use App\Models\WorkRequest;
use App\Models\Employee;
use App\Models\Holiday;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        return view('work-requests.index', compact(
            'workRequests',
            'pendingCount',
            'status',
            'type'
        ));
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

        return view('work-requests.create', compact('employee', 'upcomingHolidays'));
    }

    /**
     * POST /work-requests
     * Store new work request
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
            'request_type' => 'required|in:weekend,holiday,overtime',
            'work_date' => 'required|date|after_or_equal:today',
            'estimated_hours' => 'nullable|numeric|min:0|max:24',
            'reason' => 'nullable|string|max:500',
        ]);

        // Handle time fields separately - pass directly to model without validation
        $validated['start_time'] = $request->input('start_time');
        $validated['end_time'] = $request->input('end_time');

        // Check if there's already a pending request for this date
        $existingRequest = WorkRequest::where('employee_id', $employee->id)
            ->where('work_date', $validated['work_date'])
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending or approved request for this date.',
            ], 422);
        }

        // Validate based on request type (optional validation - HR can review during approval)
        // Commented out for now to allow requests even if holidays aren't set up in system
        /*
        if ($validated['request_type'] === 'holiday') {
            // Check if the date is actually a holiday
            if (!Holiday::isHoliday($validated['work_date'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected date is not a holiday.',
                ], 422);
            }
        } elseif ($validated['request_type'] === 'weekend') {
            // Check if the date is a weekend
            $date = Carbon::parse($validated['work_date']);
            if (!$date->isWeekend()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected date is not a weekend.',
                ], 422);
            }
        }
        */

        $workRequest = WorkRequest::create([
            'employee_id' => $employee->id,
            'request_type' => $validated['request_type'],
            'work_date' => $validated['work_date'],
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'estimated_hours' => $validated['estimated_hours'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Work request submitted successfully.',
            'work_request' => $workRequest,
        ]);
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

        return view('work-requests.show', compact('workRequest'));
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
        if ($user->isAdmin() || $user->isHR()) {
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

        return view('work-requests.edit', compact('workRequest', 'upcomingHolidays'));
    }

    /**
     * PUT /work-requests/{workRequest}
     * Update work request
     * Only employees can update their own requests
     */
    public function update(Request $request, WorkRequest $workRequest): JsonResponse
    {
 $user    = Auth::user();


        $admin = $user->isAdmin();
        $hr   = $user->isHR();

        $employee = $user->employee;

        // HR/Admin cannot update employee requests
        if ($admin || $hr) {
            return response()->json([
                'success' => false,
                'message' => 'HR/Admin cannot edit employee work requests. Use approve/reject instead.',
            ], 403);
        }

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'No employee record found for this user.',
            ], 403);
        }

        // Employees can only update their own pending requests
        if ($workRequest->employee_id !== $employee->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only edit your own work requests.',
            ], 403);
        }
        if (!$workRequest->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'You can only edit pending requests.',
            ], 403);
        }

        $validated = $request->validate([
            'request_type' => 'required|in:weekend,holiday,overtime',
            'work_date' => 'required|date|after_or_equal:today',
            'estimated_hours' => 'nullable|numeric|min:0|max:24',
            'reason' => 'nullable|string|max:500',
        ]);

        // Handle time fields separately - pass directly to model without validation
        $validated['start_time'] = $request->input('start_time');
        $validated['end_time'] = $request->input('end_time');

        // Validate based on request type (optional validation - HR can review during approval)
        // Commented out for now to allow requests even if holidays aren't set up in system
        /*
        if ($validated['request_type'] === 'holiday') {
            if (!Holiday::isHoliday($validated['work_date'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected date is not a holiday.',
                ], 422);
            }
        } elseif ($validated['request_type'] === 'weekend') {
            $date = Carbon::parse($validated['work_date']);
            if (!$date->isWeekend()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected date is not a weekend.',
                ], 422);
            }
        }
        */

        $workRequest->update([
            'request_type' => $validated['request_type'],
            'work_date' => $validated['work_date'],
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'estimated_hours' => $validated['estimated_hours'] ?? null,
            'reason' => $validated['reason'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Work request updated successfully.',
            'work_request' => $workRequest,
        ]);
    }

    /**
     * DELETE /work-requests/{workRequest}
     * Cancel work request (only pending requests)
     */
    public function destroy(WorkRequest $workRequest): JsonResponse
    {

   $user    = Auth::user();


        $admin = $user->isAdmin();
        $hr   = $user->isHR();

        $employee = $user->employee;

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'No employee record found for this user.',
            ], 403);
        }

        // Employees can only cancel their own pending requests
      if (!$admin && !$hr) {
            if ($workRequest->employee_id !== $employee->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only cancel your own work requests.',
                ], 403);
            }
            if (!$workRequest->canBeCancelled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only cancel pending requests.',
                ], 403);
            }
        }

        $workRequest->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Work request cancelled successfully.',
        ]);
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

        $employee = $user->employee;;

        if (!$admin && !$hr) {
            abort(403, 'Only administrators and HR can view pending requests.');
        }

        $pendingRequests = WorkRequest::pending()
            ->with(['employee'])
            ->orderBy('work_date', 'asc')
            ->get();

        return view('work-requests.pending', compact('pendingRequests'));
    }

    /**
     * POST /work-requests/{workRequest}/approve
     * Approve work request (HR/Admin only)
     */
    public function approve(Request $request, WorkRequest $workRequest): JsonResponse
    {
 $user    = Auth::user();


        $admin = $user->isAdmin();
        $hr   = $user->isHR();

        $employee = $user->employee;

        if (!$admin && !$hr) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators and HR can approve requests.',
            ], 403);
        }

        if (!$workRequest->canBeApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'This request cannot be approved.',
            ], 422);
        }

        $workRequest->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Work request approved successfully.',
            'work_request' => $workRequest,
        ]);
    }

    /**
     * POST /work-requests/{workRequest}/reject
     * Reject work request (HR/Admin only)
     */
    public function reject(Request $request, WorkRequest $workRequest): JsonResponse
    {

 $user    = Auth::user();


        $admin = $user->isAdmin();
        $hr   = $user->isHR();

        $employee = $user->employee;

        if (!$admin && !$hr) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators and HR can reject requests.',
            ], 403);
        }

        if (!$workRequest->canBeRejected()) {
            return response()->json([
                'success' => false,
                'message' => 'This request cannot be rejected.',
            ], 422);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $workRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Work request rejected successfully.',
            'work_request' => $workRequest,
        ]);
    }
}
