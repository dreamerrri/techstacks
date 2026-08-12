<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Payroll\PayrollComputationEngine;

class PayrollComputationEngineTest extends TestCase
{
    public function testPayrollComputation()
    {
        $engine = new PayrollComputationEngine();
        $employeeData = [
            'daily_rate' => 1000, // 22000 / 22 working days
            'working_hours_per_day' => 8,
        ];
        $attendance = [
            'days_worked' => 22,
            'overtime_hours' => 10,
            'holiday_days' => 8,
            'night_hours' => 12,
        ];
        $deductions = [500, 200];
        $benefits = [300];
        $allowances = [1000, 500];

        $result = $engine->compute($employeeData, $attendance, $deductions, $benefits, $allowances);

        $this->assertEquals(22000, $result['basic_salary']); // 1000 * 22 days worked
        $this->assertEquals(1250, $result['overtime_pay']); // 125 * 10 hours
        $this->assertEquals(16000, $result['holiday_pay']); // 1000 * 2 * 8 holiday days
        $this->assertEquals(150, $result['night_differential']); // 125 * 0.10 * 12 hours
        $this->assertEquals(1500, $result['allowances']);
        $this->assertEquals(300, $result['benefits']);
        $this->assertEquals(700, $result['deductions']);
        $this->assertEquals(41200, $result['gross_pay']); // Now includes allowances (1500)
        $this->assertEquals(40500, $result['net_pay']);
    }

    public function testPayrollComputationWithCutoffDates()
    {
        $engine = new PayrollComputationEngine();
        $employeeData = [
            'daily_rate' => 2000, // For the cutoff period
            'working_hours_per_day' => 8,
        ];
        $attendance = [
            'days_worked' => 11,
            'overtime_hours' => 5,
            'holiday_days' => 1,
            'night_hours' => 6,
        ];
        $deductions = [500, 200];
        $benefits = [300];
        $allowances = [1000, 500];

        // Test with cutoff dates (11 working days from Jan 1-15, 2026 excluding weekends)
        $result = $engine->compute(
            $employeeData,
            $attendance,
            $deductions,
            $benefits,
            $allowances,
            [],
            false,
            '2026-01-01',
            '2026-01-15'
        );

        // With 11 working days in the cutoff period, daily rate should be 2000
        $this->assertEquals(2000, $result['daily_rate']);
        $this->assertEquals(250, $result['hourly_rate']);
        $this->assertEquals(22000, $result['basic_salary']); // 2000 * 11 days worked
        $this->assertEquals(1250, $result['overtime_pay']); // 250 * 5 hours
        $this->assertEquals(4000, $result['holiday_pay']); // 2000 * 2 * 1 holiday
        $this->assertEquals(150, $result['night_differential']); // 250 * 0.10 * 6 hours
        $this->assertEquals(1500, $result['allowances']);
        $this->assertEquals(300, $result['benefits']);
        $this->assertEquals(700, $result['deductions']);
        $this->assertEquals(29200, $result['gross_pay']); // Now includes allowances (1500)
        $this->assertEquals(28500, $result['net_pay']);
    }
}
