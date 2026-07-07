<?php

namespace Tests\Unit;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that HR/Admin can update attendance records
     */
    public function test_hr_admin_can_update_attendance()
    {
        // Create an HR user
        $hrUser = User::factory()->hr()->create();
        $employee = Employee::factory()->create(['user_id' => $hrUser->id]);

        // Create an attendance record
        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
            'time_in' => '08:00',
            'time_out' => '17:00',
            'remarks' => 'Original remarks',
        ]);

        $this->actingAs($hrUser);

        $response = $this->putJson(route('employee-attendance.update', $attendance), [
            'date' => now()->toDateString(),
            'time_in' => '09:00',
            'time_out' => '18:00',
            'remarks' => 'Updated remarks',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $attendance->refresh();
        $this->assertEquals('09:00', $attendance->time_in);
        $this->assertEquals('18:00', $attendance->time_out);
        $this->assertEquals('Updated remarks', $attendance->remarks);
    }

    /**
     * Test that regular employees cannot update attendance records
     */
    public function test_employee_cannot_update_attendance()
    {
        // Create a regular employee user
        $employeeUser = User::factory()->employee()->create();
        $employee = Employee::factory()->create(['user_id' => $employeeUser->id]);

        // Create an attendance record
        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
            'time_in' => '08:00',
            'time_out' => '17:00',
            'remarks' => 'Original remarks',
        ]);

        $this->actingAs($employeeUser);

        $response = $this->putJson(route('employee-attendance.update', $attendance), [
            'date' => now()->toDateString(),
            'time_in' => '09:00',
            'time_out' => '18:00',
            'remarks' => 'Updated remarks',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Only administrators and HR can update attendance records.',
        ]);

        // Verify attendance was not changed
        $attendance->refresh();
        $this->assertEquals('08:00', $attendance->time_in);
        $this->assertEquals('17:00', $attendance->time_out);
        $this->assertEquals('Original remarks', $attendance->remarks);
    }

    /**
     * Test that auto clock-out is applied when HR/Admin edits attendance exceeding 9 hours
     */
    public function test_auto_clock_out_applied_on_hr_admin_update()
    {
        // Create an admin user
        $adminUser = User::factory()->admin()->create();
        $employee = Employee::factory()->create(['user_id' => $adminUser->id]);

        // Create an attendance record
        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
            'time_in' => '08:00',
            'time_out' => '17:00',
            'remarks' => 'Original',
        ]);

        $this->actingAs($adminUser);

        // Try to update with time exceeding 9 hours
        $response = $this->putJson(route('employee-attendance.update', $attendance), [
            'date' => now()->toDateString(),
            'time_in' => '08:00',
            'time_out' => '20:00', // This should be auto-adjusted to 18:00
            'remarks' => 'Updated',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'auto_clock_out_applied' => true,
        ]);

        $attendance->refresh();
        $this->assertEquals('08:00', $attendance->time_in);
        $this->assertEquals('18:00', $attendance->time_out); // Auto-adjusted
        $this->assertEquals(9.0, $attendance->rendered_hours);
    }
}
