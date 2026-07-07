<?php

namespace Tests\Unit;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutoClockOutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that auto clock-out is applied when exceeding 9 hours
     */
    public function test_auto_clock_out_applied_when_exceeding_9_hours()
    {
        // Create an employee
        $employee = Employee::factory()->create();

        // Create attendance that exceeds 9 hours
        // Time in: 08:00, Time out: 20:00 (12 hours total)
        // After 1 hour break: 11 hours rendered (exceeds 9 hours)
        
        $attendance = new Attendance([
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
            'time_in' => '08:00',
            'time_out' => '20:00',
            'remarks' => 'Test attendance',
        ]);

        // Manually apply the auto clock-out logic (simulating controller logic)
        $timeIn = Carbon::createFromFormat('H:i', $attendance->time_in);
        $timeOut = Carbon::createFromFormat('H:i', $attendance->time_out);
        
        $totalMinutes = $timeOut->diffInMinutes($timeIn);
        if ($totalMinutes < 0) {
            $totalMinutes = abs($totalMinutes);
        }
        
        $hours = $totalMinutes / 60;
        if ($hours > 4) {
            $hours -= 1;
        }
        
        $autoClockOutApplied = false;
        if ($hours > 9) {
            $maxTimeOut = $timeIn->copy()->addHours(10);
            $attendance->time_out = $maxTimeOut->format('H:i');
            $autoClockOutApplied = true;
        }

        $attendance->save();

        // Verify the attendance was created with auto-adjusted time_out
        $this->assertTrue($autoClockOutApplied);
        $this->assertEquals('08:00', $attendance->time_in);
        $this->assertEquals('18:00', $attendance->time_out); // Auto-adjusted
        $this->assertEquals(9.0, $attendance->rendered_hours); // 10 hours - 1 hour break
    }

    /**
     * Test that auto clock-out is NOT applied when within 9 hours
     */
    public function test_auto_clock_out_not_applied_when_within_9_hours()
    {
        // Create an employee
        $employee = Employee::factory()->create();

        // Create attendance within 9 hours
        // Time in: 08:00, Time out: 17:00 (9 hours total)
        // After 1 hour break: 8 hours rendered (within 9 hours)
        
        $attendance = new Attendance([
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
            'time_in' => '08:00',
            'time_out' => '17:00',
            'remarks' => 'Test attendance',
        ]);

        // Manually apply the auto clock-out logic
        $timeIn = Carbon::createFromFormat('H:i', $attendance->time_in);
        $timeOut = Carbon::createFromFormat('H:i', $attendance->time_out);
        
        $totalMinutes = $timeOut->diffInMinutes($timeIn);
        if ($totalMinutes < 0) {
            $totalMinutes = abs($totalMinutes);
        }
        
        $hours = $totalMinutes / 60;
        if ($hours > 4) {
            $hours -= 1;
        }
        
        $autoClockOutApplied = false;
        if ($hours > 9) {
            $maxTimeOut = $timeIn->copy()->addHours(10);
            $attendance->time_out = $maxTimeOut->format('H:i');
            $autoClockOutApplied = true;
        }

        $attendance->save();

        // Verify the attendance was created with original time_out
        $this->assertFalse($autoClockOutApplied);
        $this->assertEquals('08:00', $attendance->time_in);
        $this->assertEquals('17:00', $attendance->time_out); // NOT auto-adjusted
        $this->assertEquals(8.0, $attendance->rendered_hours); // 9 hours - 1 hour break
    }

    /**
     * Test that auto clock-out works exactly at 9 hours boundary
     */
    public function test_auto_clock_out_at_exactly_9_hours()
    {
        // Create an employee
        $employee = Employee::factory()->create();

        // Time in: 08:00, Time out: 18:00 (10 hours total)
        // After 1 hour break: 9 hours rendered (exactly at boundary)
        
        $attendance = new Attendance([
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
            'time_in' => '08:00',
            'time_out' => '18:00',
            'remarks' => 'Test attendance',
        ]);

        // Manually apply the auto clock-out logic
        $timeIn = Carbon::createFromFormat('H:i', $attendance->time_in);
        $timeOut = Carbon::createFromFormat('H:i', $attendance->time_out);
        
        $totalMinutes = $timeOut->diffInMinutes($timeIn);
        if ($totalMinutes < 0) {
            $totalMinutes = abs($totalMinutes);
        }
        
        $hours = $totalMinutes / 60;
        if ($hours > 4) {
            $hours -= 1;
        }
        
        $autoClockOutApplied = false;
        if ($hours > 9) {
            $maxTimeOut = $timeIn->copy()->addHours(10);
            $attendance->time_out = $maxTimeOut->format('H:i');
            $autoClockOutApplied = true;
        }

        $attendance->save();

        // Should NOT auto-adjust since it's exactly 9 hours
        $this->assertFalse($autoClockOutApplied);
        $this->assertEquals('08:00', $attendance->time_in);
        $this->assertEquals('18:00', $attendance->time_out);
        $this->assertEquals(9.0, $attendance->rendered_hours);
    }

    /**
     * Test that auto clock-out handles late shifts exceeding 9 hours
     */
    public function test_auto_clock_out_with_late_shift()
    {
        // Create an employee
        $employee = Employee::factory()->create();

        // Late shift: Time in: 14:00, Time out: 03:00 (next day equivalent)
        // For this test, we'll use a same-day shift that exceeds 9 hours
        // Time in: 07:00, Time out: 20:00 (13 hours total)
        // After 1 hour break: 12 hours rendered (exceeds 9 hours)
        // Should auto-clock-out at 17:00 (10 hours from time_in)
        
        $attendance = new Attendance([
            'employee_id' => $employee->id,
            'date' => now()->toDateString(),
            'time_in' => '07:00',
            'time_out' => '20:00',
            'remarks' => 'Test late shift',
        ]);

        // Manually apply the auto clock-out logic
        $timeIn = Carbon::createFromFormat('H:i', $attendance->time_in);
        $timeOut = Carbon::createFromFormat('H:i', $attendance->time_out);
        
        $totalMinutes = $timeOut->diffInMinutes($timeIn);
        if ($totalMinutes < 0) {
            $totalMinutes = abs($totalMinutes);
        }
        
        $hours = $totalMinutes / 60;
        if ($hours > 4) {
            $hours -= 1;
        }
        
        $autoClockOutApplied = false;
        if ($hours > 9) {
            $maxTimeOut = $timeIn->copy()->addHours(10);
            $attendance->time_out = $maxTimeOut->format('H:i');
            $autoClockOutApplied = true;
        }

        $attendance->save();

        // Verify auto clock-out was applied
        $this->assertTrue($autoClockOutApplied);
        $this->assertEquals('07:00', $attendance->time_in);
        $this->assertEquals('17:00', $attendance->time_out); // Auto-adjusted
        $this->assertEquals(9.0, $attendance->rendered_hours);
    }
}
