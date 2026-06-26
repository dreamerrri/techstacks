<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\WorkRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkRequestDateFilteringTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that approved work requests are only included in payroll when work date has passed.
     */
    public function test_approved_work_requests_only_credited_after_work_date()
    {
        // 1. Create a user and employee
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'basic_salary' => 40000,
            'salary_type' => 'Monthly',
            'employment_status' => 'Regular',
            'date_hired' => now(),
            'is_archived' => false,
        ]);

        // 2. Create payroll period for June 2026
        $period = PayrollPeriod::create([
            'cutoff_start' => '2026-06-01',
            'cutoff_end' => '2026-06-15',
            'payroll_date' => '2026-06-15',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        // 3. Create approved work requests
        // Past date (June 5, 2026) - should be included
        WorkRequest::create([
            'employee_id' => $employee->id,
            'request_type' => 'weekend',
            'work_date' => '2026-06-05',
            'estimated_hours' => null,
            'reason' => 'Weekend work',
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        // Future date (June 20, 2026) - should NOT be included
        WorkRequest::create([
            'employee_id' => $employee->id,
            'request_type' => 'overtime',
            'work_date' => '2026-06-20',
            'estimated_hours' => 4,
            'reason' => 'Future overtime',
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        // Another past date (June 10, 2026) - should be included
        WorkRequest::create([
            'employee_id' => $employee->id,
            'request_type' => 'holiday',
            'work_date' => '2026-06-10',
            'estimated_hours' => null,
            'reason' => 'Holiday work',
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        // 4. Test the query logic directly (simulating showPeriod logic)
        // Set current date to June 15, 2026
        $this->travelTo('2026-06-15');

        $approvedRequests = WorkRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('work_date', [$period->cutoff_start->toDateString(), $period->cutoff_end->toDateString()])
            ->where('work_date', '<=', now()->toDateString())
            ->get();

        // Should only include requests with work dates on or before June 15
        $this->assertCount(2, $approvedRequests);
        $this->assertEquals(1, $approvedRequests->where('request_type', 'weekend')->count());
        $this->assertEquals(1, $approvedRequests->where('request_type', 'holiday')->count());
        $this->assertEquals(0, $approvedRequests->where('request_type', 'overtime')->count());

        // 5. Test when current date is before the work date
        $this->travelTo('2026-06-08');

        $approvedRequestsEarly = WorkRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('work_date', [$period->cutoff_start->toDateString(), $period->cutoff_end->toDateString()])
            ->where('work_date', '<=', now()->toDateString())
            ->get();

        // Should only include June 5 request (June 10 is still in the future)
        $this->assertCount(1, $approvedRequestsEarly);
        $this->assertEquals('weekend', $approvedRequestsEarly->first()->request_type);

        // 6. Test when current date is after all work dates
        $this->travelTo('2026-06-25');

        $approvedRequestsLate = WorkRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('work_date', [$period->cutoff_start->toDateString(), $period->cutoff_end->toDateString()])
            ->where('work_date', '<=', now()->toDateString())
            ->get();

        // Should include all requests within the period (June 1-15)
        // The June 20 request is outside the period, so still not included
        $this->assertCount(2, $approvedRequestsLate);

        $this->travelBack();
    }

    /**
     * Test that pending requests are never included regardless of work date.
     */
    public function test_pending_work_requests_never_included()
    {
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'basic_salary' => 35000,
            'salary_type' => 'Monthly',
            'employment_status' => 'Regular',
            'date_hired' => now(),
            'is_archived' => false,
        ]);

        $period = PayrollPeriod::create([
            'cutoff_start' => '2026-06-01',
            'cutoff_end' => '2026-06-15',
            'payroll_date' => '2026-06-15',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        // Create pending request with past date
        WorkRequest::create([
            'employee_id' => $employee->id,
            'request_type' => 'weekend',
            'work_date' => '2026-06-05',
            'estimated_hours' => null,
            'reason' => 'Pending weekend work',
            'status' => 'pending',
        ]);

        $this->travelTo('2026-06-15');

        $approvedRequests = WorkRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('work_date', [$period->cutoff_start->toDateString(), $period->cutoff_end->toDateString()])
            ->where('work_date', '<=', now()->toDateString())
            ->get();

        // Pending requests should not be included
        $this->assertCount(0, $approvedRequests);

        $this->travelBack();
    }

    /**
     * Test calculation of special work from approved requests with date filtering.
     */
    public function test_special_work_calculation_with_date_filtering()
    {
        $user = User::factory()->create();
        $employee = Employee::create([
            'user_id' => $user->id,
            'first_name' => 'Bob',
            'last_name' => 'Johnson',
            'basic_salary' => 45000,
            'salary_type' => 'Monthly',
            'employment_status' => 'Regular',
            'date_hired' => now(),
            'is_archived' => false,
        ]);

        $period = PayrollPeriod::create([
            'cutoff_start' => '2026-06-01',
            'cutoff_end' => '2026-06-15',
            'payroll_date' => '2026-06-15',
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        // Create multiple approved requests
        WorkRequest::create([
            'employee_id' => $employee->id,
            'request_type' => 'weekend',
            'work_date' => '2026-06-06',
            'estimated_hours' => null,
            'reason' => 'Weekend 1',
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        WorkRequest::create([
            'employee_id' => $employee->id,
            'request_type' => 'weekend',
            'work_date' => '2026-06-13',
            'estimated_hours' => null,
            'reason' => 'Weekend 2',
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        WorkRequest::create([
            'employee_id' => $employee->id,
            'request_type' => 'overtime',
            'work_date' => '2026-06-07',
            'estimated_hours' => 3,
            'reason' => 'Overtime 1',
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        WorkRequest::create([
            'employee_id' => $employee->id,
            'request_type' => 'overtime',
            'work_date' => '2026-06-20', // Future date
            'estimated_hours' => 5,
            'reason' => 'Future overtime',
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        $this->travelTo('2026-06-15');

        $approvedRequests = WorkRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('work_date', [$period->cutoff_start->toDateString(), $period->cutoff_end->toDateString()])
            ->where('work_date', '<=', now()->toDateString())
            ->get();

        // Calculate special work (simulating controller logic)
        $weekendsWorked = $approvedRequests->where('request_type', 'weekend')->count();
        $overtimeHours = $approvedRequests->where('request_type', 'overtime')->sum('estimated_hours');
        $holidayDays = $approvedRequests->where('request_type', 'holiday')->count();

        // Should include 2 weekend requests and 1 overtime request (3 hours)
        // Future overtime (5 hours) should not be included
        $this->assertEquals(2, $weekendsWorked);
        $this->assertEquals(3, $overtimeHours);
        $this->assertEquals(0, $holidayDays);

        $this->travelBack();
    }
}
